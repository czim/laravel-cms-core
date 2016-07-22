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
    const AUTHENTICATED = 'cms\auth';
    const GUEST         = 'cms\guest';
    const PERMISSION    = 'cms\permission';
}
