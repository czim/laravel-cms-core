<?php
namespace Czim\CmsCore\Contracts\Auth;

interface UserInterface
{

    /**
     * Returns the login name of the user.
     *
     * @return string
     */
    public function getUsername(): string;

    /**
     * Returns whether the user is a top-level admin.
     *
     * @return bool
     */
    public function isAdmin(): bool;

    /**
     * Returns all roles for the user.
     *
     * @return string[]
     */
    public function getAllRoles(): array;

    /**
     * Returns whether the user has the given role.
     *
     * @param string $role
     * @return bool
     */
    public function hasRole(string $role): bool;

    /**
     * Returns all permissions for the user, whether by role or for the user itself.
     *
     * @return string[]
     */
    public function getAllPermissions(): array;

    /**
     * Returns whether the user has the given permission.
     *
     * @param string|string[] $permission
     * @param bool            $allowAny     if true, allows if any is permitted
     * @return bool
     */
    public function can(string $permission, bool $allowAny = false): bool;

    /**
     * Returns whether the current user has any of the given permissions.
     *
     * @param string[] $permissions
     * @return bool
     */
    public function canAnyOf(array $permissions): bool;

}
