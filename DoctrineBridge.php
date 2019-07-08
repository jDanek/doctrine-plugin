<?php declare(strict_types=1);

namespace SunlightExtend\Doctrine;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\EventManager;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\Common\Proxy\AbstractProxyFactory;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Mysqli\Driver;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Doctrine\ORM\Tools\Setup;
use Sunlight\Core;
use Sunlight\Database\Database;
use Sunlight\Extend;

abstract class DoctrineBridge
{
    /** @var EntityManager|null */
    private static $em;

    static function getEntityManager(): EntityManager
    {
        return self::$em ?? (self::createEntityManager(Database::getMysqli()));
    }

    private static function createEntityManager(\mysqli $mysqli): EntityManager
    {
        if (!Core::isReady()) {
            throw new \LogicException('Cannot use Doctrine bridge before full system initialization');
        }

        $eventManager = new EventManager();
        $eventManager->addEventListener(Events::loadClassMetadata, new ClassMetadataListener());

        $mysqliConnection = new ReusedMysqliConnection($mysqli);
        $connection = new Connection(['pdo' => $mysqliConnection], new Driver(), null, $eventManager);
        $connection->getConfiguration()->setSQLLogger(new SunlightSqlLogger());

        $cache = new SunlightCacheAdapter(Core::$cache->getNamespace('doctrine.'));
        $metadataDriver = new MappingDriverChain();

        $config = Setup::createConfiguration(false, _root . 'system/cache/doctrine-proxy', $cache);
        $config->setMetadataDriverImpl($metadataDriver);
        $config->setNamingStrategy(new UnderscoreNamingStrategy());
        $config->setAutoGenerateProxyClasses(AbstractProxyFactory::AUTOGENERATE_FILE_NOT_EXISTS);

        Extend::call('doctrine.init', [
            'connection' => $connection,
            'config' => $config,
            'metadata_driver' => $metadataDriver,
            'event_manager' => $eventManager,
        ]);

        $mapping = [];
        Extend::call('doctrine.map_entities', ['mapping' => &$mapping]);

        $drivers = [];

        if (!empty($mapping['annotation'])) {
            $drivers += self::createAnnotationDrivers($mapping['annotation']);
        }
        if (!empty($mapping['xml'])) {
            $drivers += self::createXmlDrivers($mapping['xml']);
        }

        foreach ($drivers as $namespace => $driver) {
            $metadataDriver->addDriver($driver, $namespace);
        }

        return EntityManager::create($connection, $config, $eventManager);
    }

    private static function createAnnotationDrivers(array $entityNamespaceToPaths): array
    {
        AnnotationRegistry::registerFile(__DIR__ . '/vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php');

        $driver = new AnnotationDriver(new AnnotationReader(), self::getEntityPaths($entityNamespaceToPaths));

        return self::mapDriverToEntities($driver, $entityNamespaceToPaths);
    }

    private static function createXmlDrivers(array $entityNamespaceToPaths): array
    {
        $driver = new SimplifiedXmlDriver(self::getEntityPathToNamespace($entityNamespaceToPaths), '.xml');

        return self::mapDriverToEntities($driver, $entityNamespaceToPaths);
    }

    /**
     * @return string[]
     */
    private static function getEntityPaths(array $entityNamespaceToPaths): array
    {
        $paths = [];

        foreach ($entityNamespaceToPaths as $entityPaths) {
            foreach ((array) $entityPaths as $entityPath) {
                $paths[] = $entityPath;
            }
        }

        return $paths;
    }

    private static function getEntityPathToNamespace(array $entityNamespaceToPaths): array
    {
        $entityPathToNamespace = [];

        foreach ($entityNamespaceToPaths as $namespace => $entityPaths) {
            foreach ((array) $entityPaths as $entityPath) {
                $entityPathToNamespace[$entityPath] = $namespace;
            }
        }

        return $entityPathToNamespace;
    }

    private static function mapDriverToEntities(MappingDriver $driver, array $entityNamespaceToPaths): array
    {
        return array_fill_keys(array_keys($entityNamespaceToPaths), $driver);
    }
}