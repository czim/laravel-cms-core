<?php
namespace Czim\CmsCore\Contracts\Auth;

use Illuminate\Support\Collection;

interface AuthRepositoryInterface
{

    /**
     * Returns a user by their ID, if it exists.
     *
     * @param mixed $id
     */
    public function getUserById($id);

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
     * Returns all roles known by the authenticator.
     *
     * @return string[]
     */
    public function getAllRoles();

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
