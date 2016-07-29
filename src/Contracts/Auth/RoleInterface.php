<?php
namespace Czim\CmsCore\Contracts\Auth;

interface RoleInterface
{

    /**
     * Returns the key/slug for the role.
     *
     * @return string
     */
    public function getSlug();

    /**
     * Returns the display name.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns all permissions for the role.
     *
     * @return string[]
     */
    public function getAllPermissions();

}
