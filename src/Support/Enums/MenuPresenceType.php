<?php
namespace Czim\CmsCore\Support\Enums;

use MyCLabs\Enum\Enum;

class MenuPresenceType extends Enum
{
    public const GROUP  = 'group';     // Group container (should have children)
    public const ACTION = 'action';    // CMS Route action (array or string)
    public const LINK   = 'link';      // Simple hyperlink
    public const HTML   = 'html';      // Custom HTML content
}
