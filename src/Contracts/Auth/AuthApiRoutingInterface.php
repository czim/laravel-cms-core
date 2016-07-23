<?php
namespace Czim\CmsCore\Contracts\Auth;

interface AuthApiRoutingInterface
{

    /**
     * Returns router action for the CMS API authentication.
     *
     * @return string|array
     */
    public function getApiRouteLoginAction();

    /**
     * Returns router action for logging out of the CMS for the API.
     *
     * @return string|array
     */
    public function getApiRouteLogoutAction();

}
