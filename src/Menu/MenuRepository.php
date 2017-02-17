<?php
namespace Czim\CmsCore\Menu;

use Czim\CmsCore\Contracts\Menu\MenuRepositoryInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Modules\Data\MenuPresenceInterface;
use Czim\CmsCore\Contracts\Modules\ModuleInterface;
use Czim\CmsCore\Support\Data\MenuPresence;
use Czim\CmsCore\Support\Enums\MenuPresenceType;

class MenuRepository implements MenuRepositoryInterface
{

    /**
     * @var CoreInterface
     */
    protected $core;

    /**
     * @var AuthenticatorInterface
     */
    protected $auth;

    /**
     * Whether the menu data has been prepared, used to prevent
     * performing preparations more than once.
     *
     * @var bool
     */
    protected $prepared = false;

    /**
     * Module configuration section for menu module overrides and
     * group assignment.
     *
     * @var array
     */
    protected $configMenuModels;

    /**
     * List of groups as a nested structure of presences.
     *
     * @var Collection|MenuPresenceInterface[]
     */
    protected $menuGroups;

    /**
     * Dot notation index for a group's nested location, keyed by the group key/id.
     *
     * @var string[]
     */
    protected $menuGroupIndex;

    /**
     * Ungrouped modules (those not assigned to any groups).
     *
     * @var Collection|MenuPresenceInterface[]
     */
    protected $menuUngrouped;

    /**
     * Alternative presences that do not appear in the normal menu display.
     *
     * @var Collection|MenuPresenceInterface[]
     */
    protected $alternativePresences;

    /**
     * @param CoreInterface          $core
     * @param AuthenticatorInterface $auth
     */
    public function __construct(CoreInterface $core, AuthenticatorInterface $auth)
    {
        $this->core = $core;
        $this->auth = $auth;

        $this->configMenuModels = $this->core->moduleConfig('menu.modules', []);
    }

    /**
     * Prepares the menu for presentation on demand.
     */
    public function initialize()
    {
        $this->prepareForPresentation();
    }

    /**
     * @return Collection|MenuPresenceInterface[]
     */
    public function getMenuGroups()
    {
        return $this->menuGroups;
    }

    /**
     * @return Collection|MenuPresenceInterface[]
     */
    public function getMenuUngrouped()
    {
        return $this->menuUngrouped;
    }

    /**
     * @return Collection|MenuPresenceInterface[]
     */
    public function getAlternativePresences()
    {
        return $this->alternativePresences;
    }


    // ------------------------------------------------------------------------------
    //      Processing and preparation
    // ------------------------------------------------------------------------------

    /**
     * Prepares CMS data for presentation in the menu views.
     */
    protected function prepareForPresentation()
    {
        if ($this->prepared) {
            return;
        }

        $this->menuGroups           = new Collection;
        $this->menuGroupIndex       = [];
        $this->menuUngrouped        = new Collection;
        $this->alternativePresences = new Collection;

        $this->loadMenuGroupStructure()
             ->loadMenuModules()
             ->filterEmptyGroups();
    }

    /**
     * @return $this
     */
    protected function loadMenuGroupStructure()
    {
        $groupData = $this->core->moduleConfig('menu.groups', []);

        $this->menuGroups = new Collection(
            $this->convertGroupConfigToPresenceStructure($groupData)
        );

        return $this;
    }

    /**
     * Converts an array with menu group data to an array with presence instances.
     * Recursive.
     *
     * @param array  $groupData
     * @param string $nestingPrefix
     * @return array|\Czim\CmsCore\Contracts\Modules\Data\MenuPresenceInterface[]
     */
    protected function convertGroupConfigToPresenceStructure(array $groupData, $nestingPrefix = '')
    {
        $groups = [];

        foreach ($groupData as $id => $data) {

            $children = [];

            if (array_has($data, 'children')) {
                $children = $this->convertGroupConfigToPresenceStructure(
                    array_get($data, 'children'),
                    $nestingPrefix . $id . '.'
                );
            }

            $groups[ $id ] = $this->createGroupPresence(
                $id,
                array_get($data, 'label'),
                array_get($data, 'label_translated'),
                $children
            );

            // Update the index, so we can find this group's position by its id
            $this->menuGroupIndex[ $id ] = $nestingPrefix . $id;
        }

        return $groups;
    }

    /**
     * Loads the modules from the module manager and assigns them to the
     * configured groups.
     *
     * @return $this
     */
    protected function loadMenuModules()
    {
        // Gather all modules with any menu inherent or overridden presence
        // and store them locally according to their nature.
        // Sort the modules according to their order in the menu configuration.

        $modules = $this->core->modules()->getModules();

        $modules = $this->sortModules($modules);

        foreach ($modules as $moduleKey => $module) {

            // If the configuration has overriding data for this module's presence,
            // use it. Otherwise load the menu's own presence.
            // Normalize single & plural presences for each module.

            if ($this->isConfiguredModulePresenceDisabled($module)) {
                continue;
            }

            $configuredPresencesForModule = $this->getConfiguredModulePresence($module);
            $presencesForModule = $module->getMenuPresence();
            $presencesForModule = $this->mergeNormalizedMenuPresences($presencesForModule, $configuredPresencesForModule);

            // If a module has no presence, skip it
            if ( ! $presencesForModule) {
                continue;
            }

            foreach ($this->normalizeMenuPresence($presencesForModule) as $presence) {

                $presence = $this->filterUnpermittedFromPresence($presence);

                if ( ! $presence) {
                    continue;
                }


                if ($this->isMenuPresenceAlternative($presence)) {
                    // If a menu presence is deemed 'alternative', it should not
                    // appear in the normal menu structure.

                    $this->alternativePresences->push($presence);

                } else {
                    // Otherwise, determine whether it is assigned to a group
                    // or should be left 'ungrouped'.

                    $groupKey  = $this->getConfiguredModuleGroupKey($module);
                    $menuGroup = $this->findMenuGroupPresenceByKey($groupKey);

                    if (false === $menuGroup) {
                        $this->menuUngrouped->push($presence);
                    } else {
                        /** @var MenuPresenceInterface $menuGroup */
                        $menuGroup->addChild($presence);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Sorts the modules according as configured.
     *
     * @param Collection $modules
     * @return Collection
     */
    protected function sortModules(Collection $modules)
    {
        $index = 0;

        // Make a mapping to easily look up configured order with
        $orderMap = array_flip(array_keys(config('cms-modules.menu.modules', [])));

        return $modules->sort(function (ModuleInterface $module) use (&$index, $orderMap) {

            // Order by configured order first, natural modules order second.
            $primaryOrder = array_get($orderMap, $module->getKey(), -1);

            return $primaryOrder < 0 ? $index : (1 - 1 / $primaryOrder);
        });
    }

    /**
     * Removes any empty groups from the compiled menu trees.
     *
     * @return $this
     */
    protected function filterEmptyGroups()
    {
        // Grouped
        $remove = [];
        foreach ($this->menuGroups as $key => $presence) {
            if ( ! $this->filterNestedEmptyGroups($presence)) {
                $remove[] = $key;
            }
        }
        $this->menuGroups->forget($remove);

        // Ungrouped
        $remove = [];
        foreach ($this->menuUngrouped as $key => $presence) {
            if ( ! $this->filterNestedEmptyGroups($presence)) {
                $remove[] = $key;
            }
        }
        $this->menuUngrouped->forget($remove);

        // Alternative presences should be left as is, to be determined
        // by whatever front-end implementation they may get.

        return $this;
    }

    /**
     * Removes any empty group children from a tree structure, returning
     * the number of non-group entries.
     *
     * @param MenuPresenceInterface $presence
     * @return int  the number of non-group children found on the levels below
     */
    protected function filterNestedEmptyGroups(MenuPresenceInterface $presence)
    {
        if ($presence['type'] !== MenuPresenceType::GROUP) {
            return 1;
        }

        if ( ! $presence['children'] || ! count($presence['children'])) {
            return 0;
        }

        $remove        = [];
        $children      = $presence['children'];
        $nonGroupCount = 0;

        foreach ($children as $key => $childPresence) {
            if ( ! $this->filterNestedEmptyGroups($childPresence)) {
                $remove[] = $key;
                continue;
            }

            $nonGroupCount++;
        }

        foreach ($remove as $key) {
            unset($children[$key]);
        }

        $presence->setChildren($children);

        return $nonGroupCount;
    }

    /**
     * Removes any (nested) actions that are not permissible.
     * If the entire presence is not allowed, returns null.
     *
     * @param MenuPresenceInterface $presence
     * @return MenuPresenceInterface|null
     */
    protected function filterUnpermittedFromPresence(MenuPresenceInterface $presence)
    {
        if ( ! $this->shouldMenuPresenceBeShown($presence)) {
            return null;
        }

        $children = $presence->children();

        if ( ! $children) {
            return $presence;
        }

        $presence->setChildren(
            array_filter(
                array_map(
                    function ($childPresence) {
                        return $this->filterUnpermittedFromPresence($childPresence);
                    },
                    $children
                )
            )
        );

        return $presence;
    }

    /**
     * Creates a MenuPresence instance that represents a group.
     *
     * @param string                                $id
     * @param string                                $label
     * @param string|null                           $labelTranslated
     * @param array|array[]|MenuPresenceInterface[] $children
     * @return MenuPresenceInterface
     */
    protected function createGroupPresence($id, $label, $labelTranslated = null, array $children = [])
    {
        return new MenuPresence([
            'type'             => MenuPresenceType::GROUP,
            'id'               => $id,
            'label'            => $label,
            'label_translated' => $labelTranslated,
            'children'         => $children,
        ]);
    }

    /**
     * Normalizes menu presence data to an array of MenuPresence instances.
     *
     * @param $data
     * @return MenuPresenceInterface[]
     */
    protected function normalizeMenuPresence($data)
    {
        if ($data instanceof MenuPresenceInterface) {

            $data = [ $data ];

        } elseif (is_array($data) && ! Arr::isAssoc($data)) {

            $presences = [];

            foreach ($data as $nestedData) {
                $presences[] = new MenuPresence($nestedData);
            }

            $data = $presences;

        } else {

            $data = [ new MenuPresence($data) ];
        }

        /** @var MenuPresenceInterface[] $data */

        // Now normalized to an array with one or more menu presences.
        // Make sure the children of each menu presence are normalized aswell.
        foreach ($data as $index => $presence) {

            $children = $presence->children();

            if ( ! empty($children)) {
                $data[$index]->setChildren(
                    $this->normalizeMenuPresence($children)
                );
            }
        }

        return $data;
    }

    /**
     * Returns the group MenuPresence instance in the menuGroup tree by id.
     *
     * @param string|false $id         the group's id, or false if unknown
     * @return bool|MenuPresenceInterface
     */
    protected function findMenuGroupPresenceByKey($id)
    {
        if (false === $id) {
            return false;
        }

        if ( ! array_key_exists($id, $this->menuGroupIndex)) {
            return false;
        }

        return $this->getDotMenuGroupPresence($this->menuGroupIndex[ $id ]);
    }

    /**
     * Returns the group MenuPresence instance in the menuGroup tree by dot notation key.
     * Recursive.
     *
     * @param string                     $dotKey
     * @param null|MenuPresenceInterface $parent
     * @return false|MenuPresenceInterface
     */
    protected function getDotMenuGroupPresence($dotKey, $parent = null)
    {
        $keys     = explode('.', $dotKey);
        $firstKey = array_shift($keys);

        // For the top level, find the outer group and start recursion, or
        // return the outer group if it is not a nested key.
        if ( ! $parent) {
            $parent = $this->menuGroups->get($firstKey);

            if ( ! $parent) {
                return false;
            }

            if ( ! count($keys)) {
                return $parent;
            }

            return $this->getDotMenuGroupPresence(implode('.', $keys), $parent);
        }

        // For nested groups, find the relevant child and keep going deeper
        // until we get it.
        if ( ! $parent->children()) {
            return false;
        }

        if ( ! count($keys)) {
            return $parent->children()[$firstKey];
        }

        return $this->getDotMenuGroupPresence(implode('.', $keys), $parent->children()[$firstKey]);
    }

    /**
     * Returns whether a given menu presence should be displayed.
     *
     * @param MenuPresenceInterface $presence
     * @return bool
     */
    protected function shouldMenuPresenceBeShown(MenuPresenceInterface $presence)
    {
        $permissions = $presence->permissions();

        if (empty($permissions)) {
            return true;
        }

        return $this->auth->can($permissions);
    }

    /**
     * Returns whether a presence instance should be kept separate from the normal menu structure.
     *
     * @param MenuPresenceInterface $presence
     * @return bool
     */
    protected function isMenuPresenceAlternative(MenuPresenceInterface $presence)
    {
        return  $presence->type() !== MenuPresenceType::ACTION
            &&  $presence->type() !== MenuPresenceType::GROUP
            &&  $presence->type() !== MenuPresenceType::LINK;
    }

    /**
     * Returns normalized presence data from config, if an overriding custom presence was set.
     *
     * @param ModuleInterface $module
     * @return array|false
     */
    protected function getConfiguredModulePresence(ModuleInterface $module)
    {
        if (    ! array_key_exists($module->getKey(), $this->configMenuModels)
            ||  ! is_array($this->configMenuModels[ $module->getKey() ])
        ) {
            return false;
        }

        return array_get($this->configMenuModels[ $module->getKey() ], 'presence', false);
    }

    /**
     * Returns whether the module presence has deliberately been disabled in the config.
     * This can only be done by setting the module's 'presence' value to false (strict).
     *
     * @param ModuleInterface $module
     * @return bool
     */
    protected function isConfiguredModulePresenceDisabled(ModuleInterface $module)
    {
        if (    ! array_key_exists($module->getKey(), $this->configMenuModels)
            ||  ! is_array($this->configMenuModels[ $module->getKey() ])
        ) {
            return false;
        }

        return false === array_get($this->configMenuModels[ $module->getKey() ], 'presence', true);
    }

    /**
     * Returns the group a module is assigned to in the configuration, if any.
     *
     * @param ModuleInterface $module
     * @return bool
     */
    protected function getConfiguredModuleGroupKey(ModuleInterface $module)
    {
        if ( ! array_key_exists($module->getKey(), $this->configMenuModels)) {
            return false;
        }

        // If the module config value is not an array, it is interpreted as the group key
        if ( ! is_array($this->configMenuModels[ $module->getKey() ])) {
            return $this->configMenuModels[ $module->getKey() ];
        }

        return array_get($this->configMenuModels[ $module->getKey() ], 'group', false);
    }

    /**
     * Merges two normalized menu presences into one.
     *
     * @param false|MenuPresenceInterface[] $oldPresence
     * @param false|MenuPresenceInterface[] $newPresence
     * @return MenuPresenceInterface[]
     */
    protected function mergeNormalizedMenuPresences($oldPresence, $newPresence)
    {
        if (false === $oldPresence) {
            return $newPresence;
        }

        if (false === $newPresence) {
            return $oldPresence;
        }

        // todo merge the presences, overruling old for new
        // if only one presence in either set, do a real merge
        // otherwise it is too complicated and fuzzy for now.

        return $newPresence;
    }

}
