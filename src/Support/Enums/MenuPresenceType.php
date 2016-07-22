<?php
namespace Czim\CmsCore\Support\Enums;

use MyCLabs\Enum\Enum;

class MenuPresenceType extends Enum
{
    const GROUP  = 'group';     // Group container (should have children)
    const ACTION = 'action';    // CMS Route action (array or string)
    const LINK   = 'link';      // Simple hyperlink
    const HTML   = 'html';      // Custom HTML content
}
