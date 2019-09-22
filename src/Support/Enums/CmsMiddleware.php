<?php
namespace Czim\CmsCore\Support\Enums;

use MyCLabs\Enum\Enum;

/**
 * Class CmsMiddleware
 *
 * Listing of CMS-specific 'hard-coded' middleware. Since these are
 * supposed to be middleware key names, be careful not to use ':'.
 */
class CmsMiddleware extends Enum
{
    public const AUTHENTICATED = 'cms\auth';
    public const GUEST         = 'cms\guest';
    public const PERMISSION    = 'cms\permission';

    public const API_AUTHENTICATED = 'cms\api\auth';
    public const API_AUTH_OWNER    = 'cms\api\auth-owner';
}
