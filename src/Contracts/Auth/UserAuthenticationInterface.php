<?php
namespace Czim\CmsCore\Contracts\Auth;

interface UserAuthenticationInterface
{

    /**
     * Returns whether any user is currently logged in.
     *
     * @return bool
     */
    public function check(): bool;

    /**
     * Returns currently logged in user, if any.
     *
     * @return UserInterface|false
     */
    public function user();

    /**
     * Returns whether currently logged in user, if any, is (super) admin.
     *
     * @return bool
     */
    public function admin(): bool;

    /**
     * @param string $username
     * @param string $password
     * @param bool   $remember
     * @return bool
     */
    public function login(string $username, string $password, bool $remember = true): bool;

    /**
     * Performs stateless login.
     *
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function stateless(string $username, string $password): bool;

    /**
     * Forces a user to be logged in without credentials verification.
     *
     * @param UserInterface $user
     * @param bool          $remember
     * @return bool
     */
    public function forceUser(UserInterface $user, bool $remember = true): bool;

    /**
     * Forces a user to be logged in without credentials verification,
     * without persistence, and without marking it as a login.
     *
     * @param UserInterface $user
     * @return bool
     */
    public function forceUserStateless(UserInterface $user): bool;

    /**
     * @return bool
     */
    public function logout(): bool;

    /**
     * Returns whether the current user has the given role.
     *
     * @param string $role
     * @return bool
     */
    public function hasRole($role): bool;

    /**
     * Returns whether the current user has the given permission(s).
     *
     * @param string|string[] $permission
     * @param bool            $allowAny     if true, allows if any is permitted
     * @return bool
     */
    public function can($permission, bool $allowAny = false): bool;

    /**
     * Returns whether the current user has any of the given permissions.
     *
     * @param string[] $permissions
     * @return bool
     */
    public function canAnyOf(array $permissions): bool;

}
