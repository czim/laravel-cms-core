<?php
namespace Czim\CmsCore\Contracts\Auth;

interface RoleInterface
{

    /**
     * Returns the key/slug for the role.
     *
     * @return string
     */
    public function getSlug(): string;

    /**
     * Returns the display name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Returns all permissions for the role.
     *
     * @return string[]
     */
    public function getAllPermissions(): array;

}
