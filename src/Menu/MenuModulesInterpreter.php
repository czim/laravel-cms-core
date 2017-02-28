<?php
namespace Czim\CmsCore\Menu;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Menu\MenuModulesInterpreterInterface;
use Czim\CmsCore\Contracts\Modules\Data\MenuPresenceInterface;
use Czim\CmsCore\Contracts\Modules\ModuleInterface;
use Czim\CmsCore\Contracts\Support\Data\MenuConfiguredModulesDataInterface;
use Czim\CmsCore\Support\Data\Menu\ConfiguredModulesData;
use Czim\CmsCore\Support\Data\MenuPresence;
use Czim\CmsCore\Support\Enums\MenuPresenceMode;
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
        /** @var Collection|ModuleInterface[] $modules */
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

            if ($this->hasConfiguredModulePresence($module)) {

                $configuredPresencesForModule = $this->normalizeConfiguredPresence(
                    $this->getConfiguredModulePresence($module)
                );

                $presencesForModule = $this->mergeNormalizedMenuPresences(
                    $this->normalizeMenuPresence($module->getMenuPresence()),
                    $configuredPresencesForModule
                );

            } else {
                // If no configuration is set, simply normalize and use the original

                $presencesForModule = $this->normalizeMenuPresence($module->getMenuPresence());
            }


            // If a module has no presence, skip it
            if ( ! $presencesForModule) {
                continue;
            }

            $standard    = [];
            $alternative = [];

            foreach ($presencesForModule as $presence) {

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
     * Returns whether presence configuration is set for a module.
     *
     * @param ModuleInterface $module
     * @return bool
     */
    protected function hasConfiguredModulePresence(ModuleInterface $module)
    {
        return array_key_exists($module->getKey(), $this->configModules);
    }

    /**
     * Returns raw presence configuration data, if an overriding custom presence was set.
     *
     * @param ModuleInterface $module
     * @return array|false
     */
    protected function getConfiguredModulePresence(ModuleInterface $module)
    {
        if (    ! array_key_exists($module->getKey(), $this->configModules)
            ||  ! $this->configModules[ $module->getKey() ]
        ) {
            return false;
        }

        return $this->configModules[ $module->getKey() ] ?: [];
    }

    /**
     * Returns whether an array contains presence definition data.
     *
     * @param array $configured
     * @return bool
     */
    protected function isArrayPresenceDefinition(array $configured)
    {
        return !! count(
            array_intersect(
                array_keys($configured),
                [
                    'id', 'label', 'label_translated', 'type', 'action',
                    'icon', 'image', 'html', 'parameters', 'children',
                    'mode',
                ]
            )
        );
    }

    /**
     * Merges two normalized menu presences into one.
     *
     * @param false|MenuPresenceInterface[] $oldPresences
     * @param false|MenuPresenceInterface[] $newPresences
     * @return MenuPresenceInterface[]
     */
    protected function mergeNormalizedMenuPresences($oldPresences, $newPresences)
    {
        if (false === $oldPresences) {
            return $newPresences;
        }

        // Should not matter, since setting the presence to false (instead of an array)
        // will disable the presences entirely
        if (false === $newPresences) {
            return $oldPresences;
        }

        // Since menu presences are normalized as non-associative arrays, we must
        // assume that overrides or modifications are intended index for index.
        // The first presence will be enriched or overwritten with the first presence
        // listed, unless it is specifically flagged to be added.

        // Treat additions separately, splitting them off from the overriding/modifying/deleting definitions
        $additions = [];
        $removals  = [];

        /** @var MenuPresenceInterface[] $newPresences */
        $newPresences = array_values($newPresences);

        $splitForAddition = [];

        foreach ($newPresences as $index => $presence) {
            if ($presence->mode() == MenuPresenceMode::ADD) {
                $additions[] = $presence;
                $splitForAddition[] = $index;
            }
        }

        if (count($splitForAddition)) {
            foreach (array_reverse($splitForAddition) as $index) {
                unset($newPresences[$index]);
            }
        }


        /** @var MenuPresenceInterface[] $oldPresences */
        /** @var MenuPresenceInterface[] $newPresences */
        $oldPresences = array_values($oldPresences);
        $newPresences = array_values($newPresences);

        // Perform deletions, replacements or modifications
        foreach ($newPresences as $index => $presence) {

            if ( ! array_key_exists($index, $oldPresences)) {
                continue;
            }

            switch ($presence->mode()) {

                case MenuPresenceMode::DELETE:
                    $removals[] = $index;
                    continue;

                case MenuPresenceMode::REPLACE:
                    $this->enrichConfiguredPresenceData($presence);
                    $oldPresences[ $index] = $presence;
                    continue;

                // Modify is the default mode
                default:

                    // Merge children for groups that have further nested definitions
                    if (    $oldPresences[ $index ]->type() == MenuPresenceType::GROUP
                        &&  (   $presence->type() == MenuPresenceType::GROUP
                            ||  ! in_array('type', $presence->explicitKeys() ?: [])
                            )
                        &&  count($presence->children())
                    ) {
                        // Set the merged children in the new presence, so they will be merged in
                        // for the upcoming mergeMenuPresenceData call
                        if (count($oldPresences[ $index]->children())) {
                            $presence->setChildren(
                                $this->mergeNormalizedMenuPresences(
                                    $oldPresences[ $index]->children(),
                                    $presence->children()
                                )
                            );
                        }
                    }

                    $oldPresences[ $index ] = $this->mergeMenuPresenceData($oldPresences[ $index ], $presence);
                    continue;
            }
        }

        if (count($removals)) {
            foreach (array_reverse($removals) as $index) {
                unset($oldPresences[$index]);
            }
        }

        foreach ($additions as $presence) {
            $this->enrichConfiguredPresenceData($presence);
            $oldPresences[] = $presence;
        }

        return $oldPresences;
    }

    /**
     * Normalizes configured menu presence definitions.
     *
     * @param mixed $configured
     * @return false|MenuPresenceInterface[]
     */
    protected function normalizeConfiguredPresence($configured)
    {
        if ($configured instanceof MenuPresenceInterface) {
            $configured->setChildren($this->normalizeConfiguredPresence($configured->children()));
            // To determine what keys should be used, list everything that's non-empty
            $configured->setExplicitKeys(
                array_keys(array_filter($configured->toArray()))
            );
            return [ $configured ];
        }

        if (false === $configured) {
            return [ new MenuPresence([ 'mode' => MenuPresenceMode::DELETE ]) ];
        }

        if ( ! is_array($configured)) {
            return false;
        }

        // Normalize if top layer is a presence definition, instead of an array of presences
        if ($this->isArrayPresenceDefinition($configured)) {
            $configured = [ $configured ];
        }

        foreach ($configured as &$value) {

            if ($value instanceof MenuPresenceInterface) {
                $value->setChildren($this->normalizeConfiguredPresence($value->children()));
                continue;
            }

            if (false === $value) {
                $value = [ 'mode' => MenuPresenceMode::DELETE ];
            }

            if ( ! is_array($value)) {
                throw new UnexpectedValueException('Menu presence definition must be an array');
            }

            // Set the keys that were explicitly defined, to enable specific overrides only
            $keys = array_keys($value);

            if (array_key_exists('children', $value)) {
                $value['children'] = $this->normalizeConfiguredPresence($value['children']);
            }

            $value = new MenuPresence($value);
            $value->setExplicitKeys($keys);
        }

        unset ($value);

        return $configured;
    }

    /**
     * Normalizes menu presence data to an array of MenuPresence instances.
     *
     * @param $data
     * @return false|MenuPresenceInterface[]
     */
    protected function normalizeMenuPresence($data)
    {
        if ( ! $data) {
            return false;
        }

        if ($data instanceof MenuPresenceInterface) {

            $data = [ $data ];

        } elseif (is_array($data) && ! Arr::isAssoc($data)) {

            $presences = [];

            foreach ($data as $nestedData) {

                if (is_array($nestedData)) {
                    $nestedData = new MenuPresence($nestedData);
                }

                if ( ! ($nestedData instanceof MenuPresenceInterface)) {
                    throw new UnexpectedValueException(
                        'Menu presence data from array provided by module is not an array or menu presence instance'
                    );
                }

                $presences[] = $nestedData;
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
     * Merges configured new presence data into an original presence object.
     *
     * @param MenuPresenceInterface $original
     * @param MenuPresenceInterface $new
     * @return MenuPresenceInterface
     */
    protected function mergeMenuPresenceData(MenuPresenceInterface $original, MenuPresenceInterface $new)
    {
        $keys = $new->explicitKeys();

        foreach ($keys as $key) {
            $original->{$key} = $new->{$key};
        }

        return $original;
    }

    /**
     * Enriches a full, new presence data
     *
     * @param MenuPresenceInterface|MenuPresence $presence
     */
    protected function enrichConfiguredPresenceData(MenuPresenceInterface $presence)
    {
        if ( ! $presence->type()) {

            // If type is not set, it should be determined by the action or html value set
            if ($presence->action()) {

                if ($this->isStringUrl($presence->action())) {
                    $presence->type = MenuPresenceType::LINK;
                } else {
                    $presence->type = MenuPresenceType::ACTION;
                }
            }
        }
    }

    /**
     * Returns whether a string
     *
     * @param string $string
     * @return bool
     */
    protected function isStringUrl($string)
    {
        if ( ! is_string($string)) {
            return false;
        }

        return (bool) preg_match('#^(https?:)?//|^www\.#', $string);
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
