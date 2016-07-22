<?php
namespace Czim\CmsCore\Facades;

use Illuminate\Support\Facades\Facade;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Support\Enums\Component;

/**
 * Class CoreFacade
 *
 * @see CoreInterface
 */
class CoreFacade extends Facade
{

    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return Component::CORE;
    }

}
