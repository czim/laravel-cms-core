<?php
namespace Czim\CmsCore\Contracts\Auth;

interface AuthenticatorInterface extends
    UserAuthenticationInterface,
    UserManagementInterface,
    AuthRepositoryInterface,
    AuthApiRoutingInterface,
    AuthRoutingInterface
{
}
