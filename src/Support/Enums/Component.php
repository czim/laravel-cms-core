<?php
namespace Czim\CmsCore\Support\Enums;

use MyCLabs\Enum\Enum;

/**
 * Class Component
 *
 * Listing of standard CMS components as bound in Laravel's service container.
 * Note that these values will be used as config keys aswell, so be careful not
 * to use periods (.).
 */
class Component extends Enum
{
    const API         = 'cms-api';
    const AUTH        = 'cms-auth';
    const BOOTCHECKER = 'cms-bootchecker';
    const CACHE       = 'cms-cache';
    const CORE        = 'cms-core';
    const LOG         = 'cms-log';
    const MENU        = 'cms-menu';
    const MODULES     = 'cms-modules';
}
