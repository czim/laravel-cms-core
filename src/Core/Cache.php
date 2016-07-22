<?php
namespace Czim\CmsCore\Core;

use Closure;
use Illuminate\Support\Facades\Cache as IlluminateCache;
use Czim\CmsCore\Contracts\Core\CacheInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;

class Cache implements CacheInterface
{

    /**
     * @var CoreInterface
     */
    protected $core;

    /**
     * @var \Illuminate\Contracts\Cache\Repository
     */
    protected $cache;


    /**
     * @param CoreInterface $core
     */
    public function __construct(CoreInterface $core)
    {
        $this->core = $core;

        $cache = IlluminateCache::store($this->getCacheStore());

        if ($tags = $this->getCacheTags()) {
            $cache = $cache->tags($tags);
        }

        $this->cache = $cache;
    }


    /**
     * Returns the Cache store 'driver' to use.
     *
     * @return string|null
     */
    protected function getCacheStore()
    {
        return $this->core->config('cache.store');
    }

    /**
     * Returns the cacheTags to use, if any, or false if none set.
     *
     * @return string[]|false
     */
    protected function getCacheTags()
    {
        $tags = $this->core->config('cache.tags', false);

        if (false === $tags || empty($tags)) {
            return false;
        }

        return is_array($tags) ? $tags : [ $tags ];
    }

    /**
     * Determine if an item exists in the cache.
     *
     * @param  string $key
     * @return bool
     */
    public function has($key)
    {
        return $this->cache->has($key);
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return $this->cache->get($key, $default);
    }

    /**
     * Retrieve multiple items from the cache by key.
     *
     * Items not found in the cache will have a null value.
     *
     * @param  array $keys
     * @return array
     */
    public function many(array $keys)
    {
        return $this->cache->many($keys);
    }

    /**
     * Retrieve an item from the cache and delete it.
     *
     * @param  string $key
     * @param  mixed  $default
     * @return mixed
     */
    public function pull($key, $default = null)
    {
        return $this->cache->pull($key, $default);
    }

    /**
     * Store an item in the cache.
     *
     * @param  string        $key
     * @param  mixed         $value
     * @param  \DateTime|int $minutes
     */
    public function put($key, $value, $minutes = null)
    {
        $this->cache->put($key, $value, $minutes);
    }

    /**
     * Store multiple items in the cache for a given number of minutes.
     *
     * @param  array $values
     * @param  int   $minutes
     */
    public function putMany(array $values, $minutes)
    {
        $this->cache->putMany($values, $minutes);
    }

    /**
     * Store an item in the cache if the key does not exist.
     *
     * @param  string        $key
     * @param  mixed         $value
     * @param  \DateTime|int $minutes
     * @return bool
     */
    public function add($key, $value, $minutes)
    {
        return $this->cache->add($key, $value, $minutes);
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param  string $key
     * @param  mixed  $value
     */
    public function forever($key, $value)
    {
        $this->cache->forever($key, $value);
    }

    /**
     * Get an item from the cache, or store the default value.
     *
     * @param  string        $key
     * @param  \DateTime|int $minutes
     * @param  \Closure      $callback
     * @return mixed
     */
    public function remember($key, $minutes, Closure $callback)
    {
        return $this->cache->remember($key, $minutes, $callback);
    }

    /**
     * Get an item from the cache, or store the default value forever.
     *
     * @param  string   $key
     * @param  \Closure $callback
     * @return mixed
     */
    public function rememberForever($key, Closure $callback)
    {
        return $this->cache->rememberForever($key, $callback);
    }

    /**
     * Remove an item from the cache.
     *
     * @param  string $key
     * @return bool
     */
    public function forget($key)
    {
        return $this->cache->forget($key);
    }

}
