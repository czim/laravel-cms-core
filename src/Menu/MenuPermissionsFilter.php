<?php
namespace Czim\CmsCore\Menu;

use Czim\CmsCore\Contracts\Auth\UserInterface;
use Czim\CmsCore\Contracts\Menu\MenuPermissionsFilterInterface;
use Czim\CmsCore\Contracts\Modules\Data\MenuPresenceInterface;
use Czim\CmsCore\Contracts\Support\Data\MenuLayoutDataInterface;
use Czim\CmsCore\Contracts\Support\Data\MenuPermissionsIndexDataInterface;
use Czim\CmsCore\Support\Data\Menu\PermissionsIndexData;
use Czim\CmsCore\Support\Enums\MenuPresenceType;
use RuntimeException;
use UnexpectedValueException;

/**
 * Class MenuPermissionsFilter
 *
 * For filtering the full menu layout based on permissions.
 *
 * For each group, we should combine all permissions that would allow anything to be shown
 * but only if ALL or NONE of the content of that group requires permissions.
 * Groups that contain unconditional presences, should be omitted from the index.
 *
 * The idea is that parsing once, depth-first through the layout tree, while skipping
 * any unaffected nodes as early as possible, is the quickest way to do this.
 */
class MenuPermissionsFilter implements MenuPermissionsFilterInterface
{

    /**
     * @var MenuPermissionsIndexDataInterface
     */
    protected $permissionsIndex;

    /**
     * List of all permissions referenced.
     *
     * @var string[]
     */
    protected $permissions = [];

    /**
     * List of permissions and whether they are granted or not.
     *
     * @var array   associative permission => boolean
     */
    protected $userPermissions = [];


    /**
     * Builds permissions index data for a given layout.
     *
     * The purpose of the index is to allow for efficient presence filtering
     * based on changing user permissions.
     *
     * @param MenuLayoutDataInterface $layout
     * @return MenuPermissionsIndexDataInterface
     */
    public function buildPermissionsIndex(MenuLayoutDataInterface $layout)
    {
        $this->permissionsIndex = new PermissionsIndexData();
        $this->permissions      = [];

        $this->compileIndexForLayoutLayer($layout->layout());

        $this->permissions = array_unique($this->permissions);
        $this->permissionsIndex->permissions = $this->permissions;

        return $this->permissionsIndex;
    }

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
    ) {
        if ($user && ! ($user instanceof UserInterface)) {
            throw new UnexpectedValueException("filterLayout expects UserInterface or boolean false");
        }

        if ($user && $user->isAdmin()) {
            return $layout;
        }

        $this->userPermissions = [];

        if (null !== $permissionsIndex) {
            $this->permissionsIndex = $permissionsIndex;
        }

        if ( ! $this->permissionsIndex) {
            throw new RuntimeException('FilterLayout requires permissions index data to be set');
        }

        // Get access rights for all permissions
        foreach ($this->permissionsIndex->permissions() as $permission) {
            $this->userPermissions[ $permission] = $user && $user->can($permission);
        }

        // If user has all permissions, no need to walk though the tree
        if (count(array_filter($this->userPermissions)) == count($this->userPermissions)) {
            return $layout;
        }

        $layout->setLayout($this->filterLayoutLayer($layout->layout()));

        return $layout;
    }


    /**
     * Compiles data for the index for a nested node of the layout.
     *
     * Only returns permissions list for nodes for which goes that ALL children
     * have permissions, or NONE do. Mixed conditional & unconditional must be
     * parsed no matter what, anyway.
     *
     * @param MenuPresenceInterface[] $layout
     * @param array                   $parentKeys
     * @return string[]|false     permissions, or false if the layer has mixed (un)conditional presences
     */
    protected function compileIndexForLayoutLayer(array $layout, $parentKeys = [])
    {
        $permissions = [];

        $unconditional = false;

        foreach ($layout as $key => $value) {

            if ($value->type() === MenuPresenceType::GROUP) {

                $childKeys = $parentKeys;
                array_push($childKeys, $key);

                $childPermissions = $this->compileIndexForLayoutLayer($value->children(), $childKeys);

                // If the children of the group have unconditional presences,
                // this layer becomes unconditionally displayable as well.
                if (false === $childPermissions) {
                    $unconditional = true;
                } else {
                    $permissions = array_merge($permissions, $childPermissions);
                }

                continue;
            }

            if ( ! count($value->permissions())) {
                $unconditional = true;
                continue;
            }

            $permissions = array_merge($permissions, $value->permissions());
        }

        $this->permissions = array_merge($this->permissions, $permissions);

        if ($unconditional && count($permissions)) {
            return false;
        }

        array_unique($permissions);

        if (count($parentKeys)) {
            $this->permissionsIndex->setForNode($parentKeys, $permissions);
        }

        return $permissions;
    }

    /**
     * Removes any presences that are not permitted (or left empty due to this)
     * returning the number of remaining entries.
     *
     * @param MenuPresenceInterface[] $presences
     * @param array                   $parentKeys
     * @return MenuPresenceInterface[]
     */
    protected function filterLayoutLayer(array $presences, array $parentKeys = [])
    {
        $remove = [];

        foreach ($presences as $key => $presence) {

            if ($presence->type() !== MenuPresenceType::GROUP) {

                if ( ! $this->userHasPermission($presence->permissions())) {
                    $remove[] = $key;
                }
                continue;
            }

            // Use the permissions index to determine whether we need to analyse
            // the children of this node: not if all children are unconditional
            // (index is empty array) or user has all permissions.

            $index = $this->permissionsIndex->getForNode($parentKeys);

            if (is_array($index) && ( ! count($index) || $this->userHasAllPermissions($index))) {
                continue;
            }

            $childKeys = $parentKeys;
            array_push($childKeys, $key);

            $presence->setChildren(
                $this->filterLayoutLayer($presence->children(), $childKeys)
            );

            if ( ! count($presence->children())) {
                $remove[] = $key;
            }
        }

        if (count($remove)) {
            array_forget($presences, $remove);
        }

        return $presences;
    }

    /**
     * Returns whether user has any of the given permissions.
     *
     * @param string[] $permissions
     * @return bool
     */
    protected function userHasPermission(array $permissions)
    {
        return !! count(
            array_filter($permissions, function ($permission) {
                return array_get($this->userPermissions, $permission);
            })
        );
    }

    /**
     * Returns whether user has all of the given permissions.
     *
     * @param string[] $permissions
     * @return bool
     */
    protected function userHasAllPermissions(array $permissions)
    {
        return ! count(
            array_filter($permissions, function ($permission) {
                return ! array_get($this->userPermissions, $permission);
            })
        );
    }

}
