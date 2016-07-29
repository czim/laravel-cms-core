<?php
namespace Czim\CmsCore\Contracts\Auth;

use Illuminate\Support\Collection;

interface AuthRepositoryInterface
{

    /**
     * Returns a user by their ID, if it exists.
     *
     * @param mixed $id
     * @return UserInterface|null
     */
    public function getUserById($id);

    /**
     * Returns a user by their username/email, if it exists.
     *
     * @param string $username
     * @return UserInterface|null
     */
    public function getUserByUserName($username);

    /**
     * Returns all CMS users.
     *
     * @param bool $withAdmin   include superadmins
     * @return array|Collection|UserInterface[]
     */
    public function getAllUsers($withAdmin = false);

    /**
     * Returns all CMS users with the given role.
     *
     * @param string $role
     * @param bool $withAdmin   include superadmins
     * @return array|Collection|UserInterface[]
     */
    public function getUsersForRole($role, $withAdmin = false);

    /**
     * Returns whether a given role exists.
     *
     * @param string $role
     * @return bool
     */
    public function roleExists($role);

    /**
     * @param string $role
     * @return RoleInterface
     */
    public function getRole($role);

    /**
     * Returns all roles known by the authenticator.
     *
     * @return string[]
     */
    public function getAllRoles();

    /**
     * Returns whether a role is currently used at all.
     *
     * @param string $role
     * @return bool
     */
    public function roleInUse($role);

    /**
     * Returns whether a permission with the given (exact) name is currently used at all.
     *
     * @param $permission
     * @return bool
     */
    public function permissionInUse($permission);

    /**
     * Returns all permissions known by the authenticator.
     *
     * @return string[]
     */
    public function getAllPermissions();

    /**
     * Returns all permission keys for a given role.
     *
     * @param string $role
     * @return string[]
     */
    public function getAllPermissionsForRole($role);

    /**
     * Returns all permission keys for a given user.
     *
     * @param string|UserInterface $user    user: name or instance
     * @return string[]
     */
    public function getAllPermissionsForUser($user);

}
