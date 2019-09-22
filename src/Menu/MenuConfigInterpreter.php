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
use Illuminate\Support\Arr;
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
    public function interpretLayout(array $layout): MenuLayoutDataInterface
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
    protected function interpretNestedGroupLayout(array $data): array
    {
        foreach ($data as $key => &$value) {

            if ( ! is_array($value)) {
                continue;
            }

            // The child is a group, so make a presence object for it
            $children = Arr::get($value, 'children', []);
            $children = $this->interpretNestedGroupLayout($children);

            $value = new MenuPresence([
                'id'               => $key ?: Arr::get($value, 'id'),
                'type'             => MenuPresenceType::GROUP,
                'label'            => Arr::get($value, 'label'),
                'label_translated' => Arr::get($value, 'label_translated'),
                'icon'             => Arr::get($value, 'icon'),
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
    protected function mergeModulePresencesIntoLayout(array $layout, MenuConfiguredModulesDataInterface $modules): array
    {
        $standard = $modules->standard();

        $layout = $this->mergeModulePresencesIntoLayoutLayer($layout, $standard);

        // Append ungrouped presences to to the end of the layout
        $standard->each(function ($presences) use (&$layout) {
            /** @var MenuPresenceInterface[] $presences */
            foreach ($presences as $presence) {
                $layout[] = $presence;
            }
        });

        return $layout;
    }

    /**
     * Merges module menu presences into layout tree layers recursively.
     *
     * @param array      $layer
     * @param Collection $presencesPerModule    pulls presences per module if they are added to the layout
     * @return array
     */
    protected function mergeModulePresencesIntoLayoutLayer(array $layer, Collection $presencesPerModule): array
    {
        $newLayer = [];

        foreach ($layer as $key => $value) {

            $stringKey = is_string($key) && ! is_numeric($key);

            if ($value instanceof MenuPresenceInterface && $value->type() == MenuPresenceType::GROUP) {
                $value->setChildren($this->mergeModulePresencesIntoLayoutLayer($value->children, $presencesPerModule));

                if ($stringKey) {
                    $newLayer[$key] = $value;
                } else {
                    $newLayer[] = $value;
                }
                continue;
            }

            if ( ! is_string($value)) {
                throw new UnexpectedValueException(
                    'Module key reference in menu layout must be string module key reference, or array for layout group'
                );
            }

            if (in_array($value, $this->assignedModuleKeys)) {
                throw new UnexpectedValueException(
                    "Module key reference '{$value}' for menu layout is used more than once."
                );
            }

            if ( ! $presencesPerModule->has($value)) {
                throw new UnexpectedValueException(
                    "Unknown (or disabled) module key reference '{$value}' in menu layout."
                );
            }

            $this->assignedModuleKeys[] = $value;

            foreach ($presences = $presencesPerModule->pull($value) as $presence) {
                $newLayer[] = $presence;
            }
        }

        return $newLayer;
    }

    /**
     * Filters any groups from the layout that are empty.
     *
     * This is merely a safeguard for module presence definitions that
     * contain empty groups to start with.
     *
     * @param LayoutData $layout
     */
    protected function filterEmptyGroups(LayoutData $layout): void
    {
        $presences = $layout->layout;

        $remove = [];

        foreach ($presences as $key => $presence) {

            if ( ! $this->filterNestedEmptyGroups($presence)) {
                $remove[] = $key;
            }
        }
        Arr::forget($presences, $remove);

        $layout->layout = $presences;
    }

    /**
     * Removes any empty group children from a tree structure, returning
     * the number of non-group entries.
     *
     * @param MenuPresenceInterface $presence
     * @return int  the number of non-group children found on the levels below
     */
    protected function filterNestedEmptyGroups(MenuPresenceInterface $presence): int
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
