<?php
namespace Czim\CmsCore\Support\Enums;

use MyCLabs\Enum\Enum;

class MenuPresenceMode extends Enum
{
    const ADD     = 'add';      // Does not affect existing presences, adds a new one (index should be ignored)
    const DELETE  = 'delete';   // Deleting original presence, no new one added
    const MODIFY  = 'modify';   // Modifying only the set keys for the original presence
    const REPLACE = 'replace';  // Replaces contents of presence entirely, nullifying original
}
