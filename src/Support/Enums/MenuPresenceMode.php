<?php
namespace Czim\CmsCore\Support\Enums;

use MyCLabs\Enum\Enum;

class MenuPresenceMode extends Enum
{
    public const ADD     = 'add';      // Does not affect existing presences, adds a new one (index should be ignored)
    public const DELETE  = 'delete';   // Deleting original presence, no new one added
    public const MODIFY  = 'modify';   // Modifying only the set keys for the original presence
    public const REPLACE = 'replace';  // Replaces contents of presence entirely, nullifying original
}
