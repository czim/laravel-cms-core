<?php
namespace Czim\CmsCore\Support\Data;

use Czim\CmsCore\Contracts\Modules\Data\AclPresenceInterface;

/**
 * Class AclPresence
 *
 * Data container that represents the presence of a module in the ACL.
 *
 * @property int      $id
 * @property string   $type
 * @property string   $label
 * @property string   $label_translated
 * @property string[] $permissions
 */
class AclPresence extends AbstractDataObject implements AclPresenceInterface
{

    protected $attributes = [
        'permissions' => [],
    ];

    protected $rules = [
        'id'               => 'string',
        'type'             => 'string',
        'label'            => 'string',
        'label_translated' => 'string',
        'permissions'      => 'array',
        'permissions.*'    => 'string',
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
     * Returns (translated) label for display.
     *
     * @param bool $translated  returns translated label if possible
     * @return string
     */
    public function label($translated = true)
    {
        if ($translated && $key = $this->getAttribute('label_translated')) {
            if (($label = cms_trans($key)) !== $key) {
                return $label;
            }
        }

        return $this->getAttribute('label');
    }

    /**
     * Returns the translation key for the label.
     *
     * @return string
     */
    public function translationKey()
    {
        return $this->getAttribute('label_translated');
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
