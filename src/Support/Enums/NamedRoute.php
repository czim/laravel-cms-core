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
    public const HOME                = 'home';
    public const AUTH_LOGIN          = 'auth-login';
    public const AUTH_LOGOUT         = 'auth-logout';
    public const AUTH_PASSWORD_EMAIL = 'auth-password-email';
    public const AUTH_PASSWORD_RESET = 'auth-password-reset';
    public const LOCALE_SET          = 'locale-set';
}
