<?php
namespace Czim\CmsCore\Contracts\Core;

interface CacheInterface
{

    /**
     * Determine if an item exists in the cache.
     *
     * @param  string  $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * Retrieve multiple items from the cache by key.
     *
     * Items not found in the cache will have a null value.
     *
     * @param  array  $keys
     * @return array
     */
    public function many(array $keys): array;

    /**
     * Retrieve an item from the cache and delete it.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function pull(string $key, $default = null);

    /**
     * Store an item in the cache.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @param  int     $minutes
     * @return void
     */
    public function put(string $key, $value, int $minutes = null): void;

    /**
     * Store multiple items in the cache for a given number of minutes.
     *
     * @param  array  $values
     * @param  int    $minutes
     * @return void
     */
    public function putMany(array $values, int $minutes): void;

    /**
     * Store an item in the cache if the key does not exist.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @param  int     $minutes
     * @return bool
     */
    public function add(string $key, $value, int $minutes): bool;

    /**
     * Store an item in the cache indefinitely.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function forever(string $key, $value): void;

    /**
     * Get an item from the cache, or store the default value.
     *
     * @param  string    $key
     * @param  int       $minutes
     * @param  callable  $callback
     * @return mixed
     */
    public function remember(string $key, int $minutes, callable $callback);

    /**
     * Get an item from the cache, or store the default value forever.
     *
     * @param  string    $key
     * @param  callable  $callback
     * @return mixed
     */
    public function rememberForever(string $key, callable $callback);

    /**
     * Remove an item from the cache.
     *
     * @param  string $key
     * @return bool
     */
    public function forget(string $key): bool;

}
