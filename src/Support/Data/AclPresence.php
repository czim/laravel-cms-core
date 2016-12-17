<?php
namespace Czim\CmsCore\Support\Data;

use Czim\CmsCore\Contracts\Modules\Data\AclPresenceInterface;

/**
 * Class AclPresence
 *
 * Data container that represents the presence of a module in the ACL.
 */
class AclPresence extends AbstractDataObject implements AclPresenceInterface
{

    protected $attributes = [
        'translated'  => false,
        'permissions' => [],
    ];

    protected $rules = [
        'id'            => 'string',
        'type'          => 'string',
        'label'         => 'string',
        'translated'    => 'boolean',
        'permissions'   => 'array',
        'permissions.*' => 'string',
    ];

    /**
     * Returns unique identifier for the ACL presence.
     *
     * @return string
     */
    public function id()
    {
        return $this->getAttribute('id');
    }

    /**
     * Returns type of presence.
     *
     * @see \Czim\CmsCore\Support\Enums\AclPresenceType
     * @return string
     */
    public function type()
    {
        return $this->getAttribute('type');
    }

    /**
     * Returns available permission(s).
     *
     * @return null|string|string[]
     */
    public function permissions()
    {
        return $this->getAttribute('permissions') ?: [];
    }

    /**
     * Returns (translatable) label for display.
     *
     * @return string
     */
    public function label()
    {
        return $this->getAttribute('label');
    }

    /**
     * Returns wether the label() is a translation key.
     *
     * @return bool
     */
    public function translated()
    {
        return (bool) $this->getAttribute('translated');
    }

    /**
     * Replace the permissions entirely.
     *
     * @param mixed $permissions
     */
    public function setPermissions($permissions)
    {
        $this->setAttribute('permissions', $permissions);
    }

    /**
     * Removes a permission from the current child permissions.
     *
     * @param string $permission
     */
    public function removePermission($permission)
    {
        $permissions = $this->permissions();

        $permissions = array_diff($permissions, [ $permission ]);

        $this->setAttribute('permissions', $permissions);
    }

}
