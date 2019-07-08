<?php declare(strict_types=1);

namespace SunlightExtend\Doctrine;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;

/**
 * Base entity class
 *
 * Implements a basic Active Record pattern using Database::getEntityManager().
 */
class Entity
{
    /**
     * Make this entity managed and persistent
     */
    function persist(): void
    {
        static::getEntityManager()->persist($this);
    }

    /**
     * Persist and flush this entity to the database
     */
    function save(): void
    {
        $this->persist();
        static::getEntityManager()->flush($this);
    }

    /**
     * Delete and flush this entity from the database
     */
    function delete(): void
    {
        static::getEntityManager()->remove($this);
        static::getEntityManager()->flush($this);
    }

    /**
     * @return static|null
     */
    static function find($id): ?self
    {
        return static::getEntityManager()->find(static::class, $id);
    }

    /**
     * Get repository for this entity class
     */
    static function getRepository(): EntityRepository
    {
        return static::getEntityManager()->getRepository(static::class);
    }

    /**
     * Get the entity manager
     */
    protected static function getEntityManager(): EntityManager
    {
        return DoctrineBridge::getEntityManager();
    }
}
