<?php
namespace Czim\CmsCore\Facades;

use Illuminate\Support\Facades\Facade;
use Czim\CmsCore\Contracts\Core\CacheInterface;
use Czim\CmsCore\Support\Enums\Component;

/**
 * Class CacheFacade
 *
 * @see CacheInterface
 */
class CacheFacade extends Facade
{

    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return Component::CACHE;
    }

}
