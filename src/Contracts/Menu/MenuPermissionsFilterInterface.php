<?php
namespace Czim\CmsCore\Contracts\Menu;

use Czim\CmsCore\Contracts\Auth\UserInterface;
use Czim\CmsCore\Contracts\Support\Data\MenuLayoutDataInterface;
use Czim\CmsCore\Contracts\Support\Data\MenuPermissionsIndexDataInterface;

interface MenuPermissionsFilterInterface
{

    /**
     * Builds permissions index data for a given layout.
     *
     * The purpose of the index is to allow for efficient presence filtering
     * based on changing user permissions.
     *
     * @param MenuLayoutDataInterface $layout
     * @return MenuPermissionsIndexDataInterface
     */
    public function buildPermissionsIndex(MenuLayoutDataInterface $layout): MenuPermissionsIndexDataInterface;

    /**
     * Filters a given menu layout based on menu permissions.
     *
     * @param MenuLayoutDataInterface                $layout
     * @param UserInterface|false                    $user
     * @param MenuPermissionsIndexDataInterface|null $permissionsIndex
     * @return MenuLayoutDataInterface
     */
    public function filterLayout(
        MenuLayoutDataInterface $layout,
        $user,
        MenuPermissionsIndexDataInterface $permissionsIndex = null
    ): MenuLayoutDataInterface;

}
