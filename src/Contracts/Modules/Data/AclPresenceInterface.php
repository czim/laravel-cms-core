<?php
namespace Czim\CmsCore\Contracts\Modules\Data;

use Czim\DataObject\Contracts\DataObjectInterface;

interface AclPresenceInterface extends DataObjectInterface
{

    /**
     * Returns unique identifier for the ACL presence.
     *
     * @return string
     */
    public function id();

    /**
     * Returns type of presence.
     *
     * @see \Czim\CmsCore\Support\Enums\AclPresenceType
     * @return string
     */
    public function type();

    /**
     * Returns available permission(s).
     *
     * @return string[]
     */
    public function permissions();

    /**
     * Returns (translated) label for display.
     *
     * @param bool $translated  returns translated label if possible
     * @return string
     */
    public function label($translated = true);

    /**
     * Returns the translation key for the label.
     *
     * @return string
     */
    public function translationKey();

    /**
     * Replace the permissions entirely.
     *
     * @param mixed $permissions
     */
    public function setPermissions($permissions);

    /**
     * Removes a permission from the current child permissions.
     *
     * @param string $permission
     */
    public function removePermission($permission);

}
