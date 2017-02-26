<?php
namespace Czim\CmsCore\Menu;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Menu\MenuModulesInterpreterInterface;
use Czim\CmsCore\Contracts\Modules\Data\MenuPresenceInterface;
use Czim\CmsCore\Contracts\Modules\ModuleInterface;
use Czim\CmsCore\Contracts\Support\Data\MenuConfiguredModulesDataInterface;
use Czim\CmsCore\Support\Data\Menu\ConfiguredModulesData;
use Czim\CmsCore\Support\Data\MenuPresence;
use Czim\CmsCore\Support\Enums\MenuPresenceType;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use UnexpectedValueException;

class MenuModulesInterpreter implements MenuModulesInterpreterInterface
{

    /**
     * @var CoreInterface
     */
    protected $core;

    /**
     * Menu configuration entries for modules.
     *
     * @var array
     */
    protected $configModules;

    /**
     * @var ConfiguredModulesData|MenuConfiguredModulesDataInterface
     */
    protected $presences;


    /**
     * @param CoreInterface $core
     */
    public function __construct(CoreInterface $core)
    {
        $this->core          = $core;
        $this->configModules = $this->core->moduleConfig('menu.modules', []);

        $this->presences = new ConfiguredModulesData([
            'standard'    => new Collection,
            'alternative' => new Collection,
        ]);
    }


    /**
     * Interprets configured module presences and returns a normalized data object.
     *
     * @return MenuConfiguredModulesDataInterface
     */
    public function interpret()
    {
        $modules = $this->sortModules(
            $this->core->modules()->getModules()
        );

        foreach ($modules as $moduleKey => $module) {

            // If the configuration has overriding data for this module's presence,
            // use it. Otherwise load the menu's own presence.
            // Normalize single & plural presences for each module.

            if ($this->isConfiguredModulePresenceDisabled($module)) {
                continue;
            }

            $configuredPresencesForModule = $this->getConfiguredModulePresence($module);

            $presencesForModule = $this->mergeNormalizedMenuPresences(
                $module->getMenuPresence(),
                $configuredPresencesForModule
            );

            // If a module has no presence, skip it
            if ( ! $presencesForModule) {
                continue;
            }

            $standard    = [];
            $alternative = [];

            foreach ($this->normalizeMenuPresence($presencesForModule) as $presence) {

                if ( ! $presence) {
                    continue;
                }

                // If a menu presence is deemed 'alternative',
                // it should not appear in the normal menu structure.
                if ($this->isMenuPresenceAlternative($presence)) {

                    $alternative[] = $presence;
                    continue;
                }

                $standard[] = $presence;
            }

            $this->presences->standard->put($moduleKey, $standard);
            $this->presences->alternative->put($moduleKey, $alternative);
        }

        return $this->presences;
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
        // Account for possibility of either 'module key' => [] or 'module key' format in config
        $orderMap = array_map(
            function ($key) {

                if (is_string($key)) return $key;

                if ( ! is_string($this->configModules[$key])) {
                    throw new UnexpectedValueException(
                        "cms-modules.menu.modules entry '{$key}' must be string or have a non-numeric key"
                    );
                }

                return $this->configModules[$key];
            },
            array_keys($this->configModules)
        );
        $orderMap = array_flip($orderMap);


        return $modules->sortBy(function (ModuleInterface $module) use (&$index, $orderMap) {

            // Order by configured order first, natural modules order second.
            if (count($orderMap)) {
                $primaryOrder = (int) array_get($orderMap, $module->getKey(), -2) + 1;
            } else {
                $primaryOrder = -1;
            }

            return $primaryOrder < 0 ? ++$index : (1 - 1 / $primaryOrder);
        });
    }

    /**
     * Returns normalized presence data from config, if an overriding custom presence was set.
     *
     * @param ModuleInterface $module
     * @return array|false
     */
    protected function getConfiguredModulePresence(ModuleInterface $module)
    {
        if (    ! array_key_exists($module->getKey(), $this->configModules)
            ||  ! is_array($this->configModules[ $module->getKey() ])
        ) {
            return false;
        }

        return array_get($this->configModules[ $module->getKey() ], 'presence', false);
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

        // Should not matter, since setting the presence to false (instead of an array)
        // will disable the presences entirely
        if (false === $newPresence) {
            return $oldPresence;
        }

        // todo merge the presences, overruling old for new
        // if only one presence in either set, do a real merge
        // otherwise it is too complicated and fuzzy for now.

        return $newPresence;
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
     * Returns whether the module presence has deliberately been disabled in the config.
     * This can only be done by setting the module key's value to false (strict).
     *
     * @param ModuleInterface $module
     * @return bool
     */
    protected function isConfiguredModulePresenceDisabled(ModuleInterface $module)
    {
        if ( ! array_key_exists($module->getKey(), $this->configModules)) {
            return false;
        }

        return false === $this->configModules[ $module->getKey() ];
    }

}
