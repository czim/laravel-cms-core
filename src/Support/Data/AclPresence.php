<?php
namespace Czim\CmsCore\Support\Data;

use Czim\DataObject\AbstractDataObject;
use Czim\CmsCore\Contracts\Modules\Data\AclPresenceInterface;

/**
 * Class AclPresence
 *
 * Data container that represents the presence of a module in the ACL.
 */
class AclPresence extends AbstractDataObject implements AclPresenceInterface
{

    protected $rules = [
        'type'          => 'string',
        'permissions'   => 'array',
        'permissions.*' => 'string',
    ];

}
