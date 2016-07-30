<?php
namespace Czim\CmsCore\Contracts\Auth;

interface AuthenticatorInterface extends
    UserAuthenticationInterface,
    UserManagementInterface,
    AuthRepositoryInterface,
    AuthApiRoutingInterface,
    AuthRoutingInterface
{

    /**
     * Returns Authenticator component version number.
     *
     * @return string
     */
    public function version();

}
