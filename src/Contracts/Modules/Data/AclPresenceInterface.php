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
    public function id(): string;

    /**
     * Returns type of presence.
     *
     * @see \Czim\CmsCore\Support\Enums\AclPresenceType
     * @return string
     */
    public function type(): string;

    /**
     * Returns available permission(s).
     *
     * @return string[]
     */
    public function permissions(): array;

    /**
     * Returns (translated) label for display.
     *
     * @param bool $translated  returns translated label if possible
     * @return string
     */
    public function label(bool $translated = true): string;

    /**
     * Returns the translation key for the label.
     *
     * @return string|null
     */
    public function translationKey(): ?string;

    /**
     * Replace the permissions entirely.
     *
     * @param string[] $permissions
     */
    public function setPermissions(array $permissions): void;

    /**
     * Removes a permission from the current child permissions.
     *
     * @param string $permission
     */
    public function removePermission(string $permission): void;

}
