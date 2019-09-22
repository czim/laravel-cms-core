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
    public const ACL  = 'cms-acl';
    public const API         = 'cms-api';
    public const ASSETS      = 'cms-assets';
    public const AUTH        = 'cms-auth';
    public const BOOTCHECKER = 'cms-bootchecker';
    public const CACHE       = 'cms-cache';
    public const CORE        = 'cms-core';
    public const LOG         = 'cms-log';
    public const MENU        = 'cms-menu';
    public const MODULES     = 'cms-modules';
    public const NOTIFIER    = 'cms-notifier';
}
