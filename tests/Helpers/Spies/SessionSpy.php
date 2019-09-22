<?php
namespace Czim\CmsCore\Test\Helpers\Spies;

use Illuminate\Support\Arr;

/**
 * Class SessionSpy
 *
 * @see \Czim\CmsCore\Test\Core\BasicNotifierTest
 */
class SessionSpy
{
    /**
     * @var array
     */
    public $stored = [];


    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default)
    {
        return Arr::get($this->stored, $key, $default);
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function put($key, $value)
    {
        Arr::set($this->stored, $key, $value);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return Arr::has($this->stored, $key);
    }

    /**
     * @param string $key
     */
    public function remove($key)
    {
        Arr::forget($this->stored, $key);
    }

}
