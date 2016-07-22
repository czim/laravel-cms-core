<?php
namespace Czim\CmsCore\Facades;

use Illuminate\Support\Facades\Facade;
use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Support\Enums\Component;

/**
 * Class AuthenticatorFacade
 *
 * @see AuthenticatorInterface
 */
class AuthenticatorFacade extends Facade
{

    /**
     * {@inheritDoc}
     */
    protected static function getFacadeAccessor()
    {
        return Component::AUTH;
    }

}
