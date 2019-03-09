<?php
namespace Czim\CmsCore\Support\Data\Menu;

use Czim\CmsCore\Support\Data\AbstractDataObject;
use Czim\DataObject\Contracts\DataObjectInterface;

/**
 * Class LayoutGroupData
 *
 * Data container that represents a (nested) group of menu presences.
 *
 * @property int      $id
 * @property string   $type
 * @property string   $label
 * @property string   $label_translated
 * @property string   $icon
 * @property array    $children
 */
class LayoutGroupData extends AbstractDataObject
{
    protected $attributes = [
        'children' => [],
    ];

    protected $rules = [
        'id'               => 'string',
        'label'            => 'string',
        'label_translated' => 'string',
        'icon'             => 'string',
        'children'         => 'array',
    ];

    /**
     * Returns unique identifier for the menu presence.
     *
     * @return string
     */
    public function id()
    {
        return $this->getAttribute('id');
    }

    /**
     * Returns type of presence.
     *
     * @see \Czim\CmsCore\Support\Enums\MenuPresenceType
     * @return string
     */
    public function type()
    {
        return 'group';
    }

    /**
     * Returns children of this menu group.
     *
     * @return null|array
     */
    public function children()
    {
        return $this->getAttribute('children');
    }

    /**
     * Returns label for display.
     *
     * @param bool $translated  return translated label if possible
     * @return string
     */
    public function label($translated = true)
    {
        if ($translated && $key = $this->getAttribute('label_translated')) {
            if (($label = cms_trans($key)) !== $key) {
                return $label;
            }
        }

        return $this->getAttribute('label');
    }

    /**
     * Returns the translation key for the label.
     *
     * @return string
     */
    public function translationKey()
    {
        return $this->getAttribute('label_translated');
    }

    /**
     * Returns icon or image reference for display purposes.
     *
     * @return string
     */
    public function icon()
    {
        return $this->getAttribute('icon');
    }

    /**
     * Converts attributes to specific dataobjects if configured to
     *
     * @param string $key
     * @return mixed|DataObjectInterface
     */
    public function &getAttributeValue(string $key)
    {
        if ($key !== 'children') {
            return parent::getAttributeValue($key);
        }

        if ( ! isset($this->attributes[$key]) || ! is_array($this->attributes[$key])) {
            // @codeCoverageIgnoreStart
            $null = null;
            return $null;
            // @codeCoverageIgnoreEnd
        }

        // If object is an array, interpret it as a group
        $this->decorateChildrenAttribute($key);

        return $this->attributes[$key];
    }

    /**
     * @param string $topKey
     */
    protected function decorateChildrenAttribute(string $topKey = 'children')
    {
        foreach ($this->attributes[$topKey] as $key => &$value) {

            if (is_array($value)) {
                $value = new LayoutGroupData($value);
            }
        }

        unset ($value);
    }

}
