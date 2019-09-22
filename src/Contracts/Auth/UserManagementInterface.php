<?php
namespace Czim\CmsCore\Contracts\Auth;

interface UserManagementInterface
{

    /**
     * Create new CMS user.
     *
     * @param string $username
     * @param string $password
     * @param array  $data
     * @return UserInterface
     */
    public function createUser(string $username, string $password, array $data = []): UserInterface;

    /**
     * Removes a user from the CMS.
     *
     * @param string $username
     * @return bool
     */
    public function deleteUser(string $username): bool;

    /**
     * Sets a new password for an existing CMS user.
     *
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function updatePassword(string $username, string $password): bool;

    /**
     * Updates a CMS user's (extra) data.
     *
     * @param string $username
     * @param array $data
     * @return bool
     */
    public function updateUser(string $username, array $data): bool;


    // ------------------------------------------------------------------------------
    //      Roles
    // ------------------------------------------------------------------------------

    /**
     * Creates a role.
     *
     * @param string      $role
     * @param string|null $name
     * @return bool
     */
    public function createRole(string $role, ?string $name = null): bool;

    /**
     * Removes a role.
     *
     * @param string $role
     * @return bool
     */
    public function removeRole(string $role): bool;

    /**
     * Assigns one or several roles to a user.
     *
     * @param string|string[] $role
     * @param UserInterface   $user
     * @return bool
     */
    public function assign($role, UserInterface $user): bool;

    /**
     * Removes one or several roles from a user.
     *
     * @param string|string[] $role
     * @param UserInterface   $user
     * @return bool
     */
    public function unassign($role, UserInterface $user): bool;


    // ------------------------------------------------------------------------------
    //      Permissions
    // ------------------------------------------------------------------------------

    /**
     * Grants one or more permissions to a role.
     *
     * @param string|string[] $permission
     * @param string          $role
     * @return bool
     */
    public function grantToRole($permission, string $role): bool;

    /**
     * Revokes one or more permissions of a role.
     *
     * @param string|string[] $permission
     * @param string          $role
     * @return bool
     */
    public function revokeFromRole($permission, string $role): bool;

    /**
     * Grants one or more permissions to a user.
     *
     * @param string|string[] $permission
     * @param UserInterface   $user
     * @return bool
     */
    public function grant($permission, UserInterface $user): bool;

    /**
     * Revokes one or more permissions of a user.
     *
     * @param string|string[] $permission
     * @param UserInterface   $user
     * @return bool
     */
    public function revoke($permission, UserInterface $user): bool;

}
