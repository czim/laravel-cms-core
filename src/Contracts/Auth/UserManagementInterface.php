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
    public function createUser($username, $password, array $data = []);

    /**
     * Removes a user from the CMS.
     *
     * @param $username
     * @return bool
     */
    public function deleteUser($username);


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
    public function createRole($role, $name = null);

    /**
     * Removes a role.
     *
     * @param string $role
     * @return bool
     */
    public function removeRole($role);

    /**
     * Assigns one or several roles to a user.
     * 
     * @param string|string[] $role
     * @param UserInterface   $user
     * @return bool
     */
    public function assign($role, UserInterface $user);

    /**
     * Removes one or several roles from a user.
     *
     * @param string|string[] $role
     * @param UserInterface   $user
     * @return bool
     */
    public function unassign($role, UserInterface $user);
    

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
    public function grantToRole($permission, $role);

    /**
     * Revokes one or more permissions of a role.
     *
     * @param string|string[] $permission
     * @param string          $role
     * @return bool
     */
    public function revokeFromRole($permission, $role);

    /**
     * Grants one or more permissions to a user.
     *
     * @param string|string[] $permission
     * @param UserInterface   $user
     * @return bool
     */
    public function grant($permission, UserInterface $user);

    /**
     * Revokes one or more permissions of a user.
     *
     * @param string|string[] $permission
     * @param UserInterface   $user
     * @return bool
     */
    public function revoke($permission, UserInterface $user);

}
