<?php
namespace Czim\CmsCore\Contracts\Core;

interface StateInterface
{

    /**
     * Clears all global state values.
     *
     * @return $this
     */
    public function clear();

    /**
     * Sets a global state value by key.
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function set($key, $value);

    /**
     * Returns a global state setting by key.
     *
     * @param string $key
     * @return mixed
     */
    public function get($key);


}
