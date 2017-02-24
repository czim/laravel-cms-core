<?php
namespace Czim\CmsCore\Support\Data\Menu;

use Czim\CmsCore\Contracts\Modules\Data\MenuPresenceInterface;
use Czim\CmsCore\Contracts\Support\Data\MenuLayoutDataInterface;
use Czim\CmsCore\Support\Data\AbstractDataObject;
use Czim\DataObject\Contracts\DataObjectInterface;

/**
 * Class LayoutData
 *
 * Data container that represents the top level for menu presence layout,
 * along with related and alternative presences.
 *
 * @property MenuPresenceInterface[] $layout
 * @property MenuPresenceInterface[] $alternative
 */
class LayoutData extends AbstractDataObject implements MenuLayoutDataInterface
{

    protected $attributes = [
        // Top-level menu layout elements, either elements of ModelLayoutGroupData or module key strings
        'layout' => [],
        // Alternative presences, that are not part of the normal layout
        'alternative' => [],
    ];

    /**
     * Returns the layout that should be used for displaying the edit form.
     *
     * @return array|mixed[]
     */
    public function layout()
    {
        if ($this->layout && count($this->layout)) {
            return $this->layout;
        }

        return array_keys($this->fields);
    }

    /**
     * @return MenuPresenceInterface[]
     */
    public function alternative()
    {
        return $this->getAttribute('alternative');
    }

    /**
     * Converts attributes to specific dataobjects if configured to
     *
     * @param string $key
     * @return mixed|DataObjectInterface
     */
    public function &getAttributeValue($key)
    {
        if ($key !== 'layout') {
            return parent::getAttributeValue($key);
        }

        if ( ! isset($this->attributes[$key]) || ! is_array($this->attributes[$key])) {
            $null = null;
            return $null;
        }

        // If object is an array, interpret it as a group
        $this->decorateLayoutAttribute($key);

        return $this->attributes[$key];
    }

    /**
     * @param string $topKey
     */
    protected function decorateLayoutAttribute($topKey = 'layout')
    {
        foreach ($this->attributes[$topKey] as $key => &$value) {

            if (is_array($value)) {
                $value = new LayoutGroupData($value);
            }
        }

        unset ($value);
    }

}
