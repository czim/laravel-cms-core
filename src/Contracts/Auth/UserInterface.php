<?php
namespace Czim\CmsCore\Contracts\Auth;

interface UserInterface
{

    /**
     * Returns the login name of the user.
     *
     * @return string
     */
    public function getUsername();

    /**
     * Returns whether the user is a top-level admin.
     *
     * @return bool
     */
    public function isAdmin();

    /**
     * Returns whether the user has the given role.
     *
     * @param string $role
     * @return bool
     */
    public function hasRole($role);

    /**
     * Returns whether the user has the given permission.
     *
     * @param string|string[] $permission
     * @param bool            $allowAny     if true, allows if any is permitted
     * @return bool
     */
    public function can($permission, $allowAny = false);

    /**
     * Returns whether the current user has any of the given permissions.
     *
     * @param string[] $permissions
     * @return bool
     */
    public function canAnyOf(array $permissions);

}
