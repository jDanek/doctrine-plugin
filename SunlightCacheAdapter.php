<?php declare(strict_types=1);

namespace SunlightExtend\Doctrine;

use Doctrine\Common\Cache\CacheProvider;
use Kuria\Cache\CacheInterface;

class SunlightCacheAdapter extends CacheProvider
{
    /** @var CacheInterface */
    protected $cache;

    function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    protected function doFetch($id)
    {
        return $this->cache->get($this->processId($id));
    }

    protected function doContains($id)
    {
        return $this->cache->has($this->processId($id));
    }

    protected function doSave($id, $data, $lifeTime = 0)
    {
        return $this->cache->set($this->processId($id), $data, $lifeTime);
    }

    protected function doDelete($id)
    {
        return $this->cache->remove($this->processId($id));
    }

    protected function doFlush()
    {
        return $this->cache->clear();
    }

    protected function doGetStats()
    {
        return null;
    }

    private function processId(string $id): string
    {
        return hash('sha256', $id);
    }
}
