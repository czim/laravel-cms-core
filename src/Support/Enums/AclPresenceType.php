<?php
namespace Czim\CmsCore\Support\Enums;

use MyCLabs\Enum\Enum;

class AclPresenceType extends Enum
{
    public const GROUP      = 'group';         // Group container (should have children)
    public const PERMISSION = 'permission';    // CMS permission key
}
