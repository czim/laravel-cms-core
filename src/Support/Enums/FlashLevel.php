<?php
namespace Czim\CmsCore\Support\Enums;

use MyCLabs\Enum\Enum;

/**
 * Class FlashLevel
 *
 * Session flash message levels.
 */
class FlashLevel extends Enum
{
    public const DANGER  = 'danger';
    public const INFO    = 'info';
    public const SUCCESS = 'success';
    public const WARNING = 'warning';
}
