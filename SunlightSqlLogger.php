<?php declare(strict_types=1);

namespace SunlightExtend\Doctrine;

use Doctrine\DBAL\Logging\SQLLogger;
use Sunlight\Extend;

class SunlightSqlLogger implements SQLLogger
{
    /** @var string|null */
    protected $currentQuery;

    function startQuery($sql, ?array $params = null, ?array $types = null)
    {
        $this->currentQuery = $sql;

        Extend::call('db.query', ['sql' => $sql]);
    }

    function stopQuery()
    {
        $sql = $this->currentQuery;
        $this->currentQuery = null;

        Extend::call('db.query.after', [
            'sql' => $sql,
            'result' => null,
            'exception' => null,
        ]);
    }
}
