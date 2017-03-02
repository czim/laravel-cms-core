<?php
namespace Czim\CmsCore\Test\Helpers\Spies;

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
        return array_get($this->stored, $key, $default);
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function set($key, $value)
    {
        array_set($this->stored, $key, $value);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return array_has($this->stored, $key);
    }

    /**
     * @param string $key
     */
    public function remove($key)
    {
        array_forget($this->stored, $key);
    }

}
