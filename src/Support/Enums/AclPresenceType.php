<?php
namespace Czim\CmsCore\Support\Enums;

use MyCLabs\Enum\Enum;

class AclPresenceType extends Enum
{
    const GROUP      = 'group';         // Group container (should have children)
    const PERMISSION = 'permission';    // CMS permission key
}
