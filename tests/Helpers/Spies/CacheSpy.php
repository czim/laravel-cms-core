<?php
namespace Czim\CmsCore\Test\Helpers\Spies;

/**
 * Class CacheSpy
 *
 * Helper class to spy on initialization, since mocks + container
 * bindings are not able to perform expectation checks in succession.
 *
 * @see \Czim\CmsCore\Test\Core\CacheTest
 */
class CacheSpy
{
    public $storeCalled = 0;
    public $storeParameter;

    public $tagsCalled = 0;
    public $tagsParameter;

    /**
     * @param mixed $parameter
     * @return $this
     */
    public function store($parameter): CacheSpy
    {
        $this->storeParameter = $parameter;
        $this->storeCalled++;

        return $this;
    }

    /**
     * @param mixed $parameter
     * @return $this
     */
    public function tags($parameter): CacheSpy
    {
        $this->tagsParameter = $parameter;
        $this->tagsCalled++;

        return $this;
    }
}
