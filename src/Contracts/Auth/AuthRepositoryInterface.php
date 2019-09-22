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
    public function getUserById($id): ?UserInterface;

    /**
     * Returns a user by their username/email, if it exists.
     *
     * @param string $username
     * @return UserInterface|null
     */
    public function getUserByUserName(string $username): ?UserInterface;

    /**
     * Returns all CMS users.
     *
     * @param bool $withAdmin   include superadmins
     * @return Collection|UserInterface[]
     */
    public function getAllUsers(bool $withAdmin = false): Collection;

    /**
     * Returns all CMS users with the given role.
     *
     * @param string $role
     * @param bool $withAdmin   include superadmins
     * @return Collection|UserInterface[]
     */
    public function getUsersForRole(string $role, bool $withAdmin = false): Collection;

    /**
     * Returns whether a given role exists.
     *
     * @param string $role
     * @return bool
     */
    public function roleExists(string $role): bool;

    /**
     * @param string $role
     * @return RoleInterface
     */
    public function getRole(string $role): RoleInterface;

    /**
     * Returns all roles known by the authenticator.
     *
     * @return string[]
     */
    public function getAllRoles(): array;

    /**
     * Returns whether a role is currently used at all.
     *
     * @param string $role
     * @return bool
     */
    public function roleInUse(string $role): bool;

    /**
     * Returns whether a permission with the given (exact) name is currently used at all.
     *
     * @param string $permission
     * @return bool
     */
    public function permissionInUse(string $permission): bool;

    /**
     * Returns all permissions known by the authenticator.
     *
     * @return string[]
     */
    public function getAllPermissions(): array;

    /**
     * Returns all permission keys for a given role.
     *
     * @param string $role
     * @return string[]
     */
    public function getAllPermissionsForRole(string $role): array;

    /**
     * Returns all permission keys for a given user.
     *
     * @param string|UserInterface $user    user: name or instance
     * @return string[]
     */
    public function getAllPermissionsForUser($user): array;

}
