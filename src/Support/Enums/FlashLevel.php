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
    const DANGER  = 'danger';
    const INFO    = 'info';
    const SUCCESS = 'success';
    const WARNING = 'warning';
}
