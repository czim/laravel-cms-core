<?php
namespace Czim\CmsCore\Menu;

use Czim\CmsCore\Contracts\Menu\MenuConfigInterpreterInterface;
use Czim\CmsCore\Contracts\Menu\MenuModulesInterpreterInterface;
use Czim\CmsCore\Contracts\Modules\Data\MenuPresenceInterface;
use Czim\CmsCore\Contracts\Support\Data\MenuConfiguredModulesDataInterface;
use Czim\CmsCore\Contracts\Support\Data\MenuLayoutDataInterface;
use Czim\CmsCore\Support\Data\Menu\LayoutData;
use Czim\CmsCore\Support\Data\MenuPresence;
use Czim\CmsCore\Support\Enums\MenuPresenceType;
use Illuminate\Support\Collection;
use UnexpectedValueException;

/**
 * Class MenuConfigInterpreter
 *
 * For interpreting the configured menu layout and menu presences for modules.
 */
class MenuConfigInterpreter implements MenuConfigInterpreterInterface
{

    /**
     * @var MenuModulesInterpreterInterface
     */
    protected $modulesInterpreter;

    /**
     * A list of module keys for which presences have been assigned to the layout.
     *
     * @var string[]
     */
    protected $assignedModuleKeys;


    /**
     * @param MenuModulesInterpreterInterface $modulesInterpreter
     */
    public function __construct(MenuModulesInterpreterInterface $modulesInterpreter)
    {
        $this->modulesInterpreter = $modulesInterpreter;
    }


    /**
     * Interprets a configured menu layout array.
     *
     * @param array $layout
     * @return MenuLayoutDataInterface
     */
    public function interpretLayout(array $layout)
    {
        $this->assignedModuleKeys = [];

        $modulePresences = $this->modulesInterpreter->interpret();

        $layout = $this->mergeModulePresencesIntoLayout($this->interpretNestedGroupLayout($layout), $modulePresences);

        $layout = new LayoutData([
            'layout'      => $layout,
            'alternative' => $modulePresences->alternative()->toarray(),
        ]);

        $this->filterEmptyGroups($layout);

        return $layout;
    }

    /**
     * Interprets nested groups in a layout array, creating presences for them.
     *
     * @param array $data
     * @return array
     */
    protected function interpretNestedGroupLayout(array $data)
    {
        foreach ($data as $key => &$value) {

            if ( ! is_array($value)) {
                continue;
            }

            // The child is a group, so make a presence object for it
            $children = array_get($value, 'children', []);
            $children = $this->interpretNestedGroupLayout($children);

            $value = new MenuPresence([
                'id'               => $key ?: array_get($value, 'id'),
                'type'             => MenuPresenceType::GROUP,
                'label'            => array_get($value, 'label'),
                'label_translated' => array_get($value, 'label_translated'),
                'image'            => array_get($value, 'image'),
                'children'         => $children,
            ]);
        }

        unset ($value);

        return $data;
    }

    /**
     * Merges module menu presences into layout tree array.
     *
     * @param array                              $layout
     * @param MenuConfiguredModulesDataInterface $modules
     * @return array
     */
    protected function mergeModulePresencesIntoLayout(array $layout, MenuConfiguredModulesDataInterface $modules)
    {
        $standard = $modules->standard();

        $layout = $this->mergeModulePresencesIntoLayoutLayer($layout, $standard);

        // Append ungrouped presences to to the end of the layout
        $standard->each(function (MenuPresenceInterface $presence) use (&$layout) {
            $layout[] = $presence;
        });

        return $layout;
    }

    /**
     * Merges module menu presences into layout tree layers recursively.
     *
     * @param array      $layer
     * @param Collection $presences     pulls presences
     * @return array
     */
    protected function mergeModulePresencesIntoLayoutLayer(array $layer, Collection $presences)
    {
        foreach ($layer as $key => &$value) {

            if ($value instanceof MenuPresenceInterface && $value->type() == MenuPresenceType::GROUP) {
                $value->setChildren($this->mergeModulePresencesIntoLayoutLayer($value->children, $presences));
                continue;
            }

            if ( ! is_string($value)) {
                continue;
            }

            if (in_array($value, $this->assignedModuleKeys)) {
                throw new UnexpectedValueException("Module key reference '{$value}' for menu layout is used more than once.");
            }

            if ( ! $presences->has($value)) {
                throw new UnexpectedValueException("Unknown module key reference '{$value}' in menu layout.");
            }

            $this->assignedModuleKeys[] = $value;

            $value = $presences->pull($value);
        }

        unset ($value);

        return $layer;
    }

    /**
     * Filters any groups from the layout that are empty.
     *
     * This is merely a safeguard for module presence definitions that
     * contain empty groups to start with.
     *
     * @param LayoutData $layout
     */
    protected function filterEmptyGroups(LayoutData $layout)
    {
        $presences = $layout->layout;

        $remove = [];

        foreach ($presences as $key => $presence) {

            if ( ! $this->filterNestedEmptyGroups($presence)) {
                $remove[] = $key;
            }
        }
        array_forget($presences, $remove);

        $layout->layout = $presences;
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
        if ($presence->type() !== MenuPresenceType::GROUP) {
            return 1;
        }

        $children = $presence->children();

        if ( ! $children || ! count($children)) {
            return 0;
        }

        $remove        = [];
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

}
