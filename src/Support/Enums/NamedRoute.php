<?php
namespace Czim\CmsCore\Support\Enums;

use MyCLabs\Enum\Enum;

/**
 * Class NamedRoute
 *
 * Listing of 'hard-coded' standard route names for critical CMS routes.
 */
class NamedRoute extends Enum
{
    const HOME                = 'home';
    const AUTH_LOGIN          = 'auth-login';
    const AUTH_LOGOUT         = 'auth-logout';
    const AUTH_PASSWORD_EMAIL = 'auth-password-email';
    const AUTH_PASSWORD_RESET = 'auth-password-reset';
}
