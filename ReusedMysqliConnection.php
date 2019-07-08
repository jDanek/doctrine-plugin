<?php declare(strict_types=1);

namespace SunlightExtend\Doctrine;

use Doctrine\DBAL\Driver\Mysqli\MysqliConnection;

class ReusedMysqliConnection extends MysqliConnection
{
    function __construct(\mysqli $mysqli)
    {
        // hack, but better than copy-pasting the entire MysqliConnection
        $mysqliProp = new \ReflectionProperty(get_parent_class(), 'conn');
        $mysqliProp->setAccessible(true);
        $mysqliProp->setValue($this, $mysqli);
    }
}
