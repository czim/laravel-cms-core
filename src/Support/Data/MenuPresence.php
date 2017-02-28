<?php
namespace Czim\CmsCore\Support\Data;

use Czim\CmsCore\Support\Enums\MenuPresenceMode;
use Czim\CmsCore\Support\Enums\MenuPresenceType;
use Illuminate\Support\Collection;
use Czim\CmsCore\Contracts\Modules\Data\MenuPresenceInterface;

/**
 * Class MenuPresence
 *
 * Data container that represents the presence of a module in the menu.
 *
 * @property int      $id
 * @property string   $type
 * @property string   $label
 * @property string   $label_translated
 * @property string   $action
 * @property string   $icon
 * @property string   $html
 * @property array    $parameters
 * @property array    $children
 * @property string   $mode             only used for configuration, should be ignored when rendering
 * @property string[] $explicit_keys    only used for configuration, should be ignored when rendering
 */
class MenuPresence extends AbstractDataObject implements MenuPresenceInterface
{
    protected $attributes = [
        'parameters'    => [],
        'children'      => [],
        'mode'          => MenuPresenceMode::MODIFY,
        'explicit_keys' => [],
    ];

    protected $rules = [
        'id'               => 'string',
        'type'             => 'required|string',
        'label'            => 'string',
        'label_translated' => 'string',
        'action'           => 'string',
        'parameters'       => 'array',
        'icon'             => 'string',
        'children'         => 'array',
        'html'             => 'string',

        'mode'             => 'string',
        'explicit_keys'    => 'array',
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
        return $this->getAttribute('type');
    }

    /**
     * Returns child presences of this presence.
     *
     * @return MenuPresenceInterface[]
     */
    public function children()
    {
        $children = $this->getAttribute('children') ?: [];

        if ($children && ! is_array($children)) {
            return [ $children ];
        }

        return $children;
    }

    /**
     * Returns link or route, depending on type.
     *
     * @return null|string|array
     */
    public function action()
    {
        return $this->getAttribute('action');
    }

    /**
     * Returns route parameters for route actions.
     *
     * @return array
     */
    public function parameters()
    {
        return $this->getAttribute('parameters');
    }

    /**
     * Returns required permissions to see or use the action.
     *
     * @return string[]
     */
    public function permissions()
    {
        $permissions = $this->getAttribute('permissions');

        if (empty($permissions)) {
            return [];
        }

        if ( ! is_array($permissions)) {
            $permissions = [ $permissions ];
        }

        return $permissions;
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
     * Returns icon reference for display purposes.
     *
     * @return string
     */
    public function icon()
    {
        return $this->getAttribute('icon');
    }

    /**
     * Returns icon or image reference for display purposes.
     *
     * @return string
     * @deprecated
     */
    public function image()
    {
        return $this->getAttribute('icon');
    }

    /**
     * Returns configuration mode for menu presence definition.
     *
     * @return string
     * @see MenuPresenceMode
     */
    public function mode()
    {
        return $this->getAttribute('mode') ?: MenuPresenceMode::MODIFY;
    }

    /**
     * Sets keys that are explicitly configured.
     *
     * @param string[] $keys
     * @return $this
     */
    public function setExplicitKeys(array $keys)
    {
        // Filter out any keys that don't belong in this object
        $keys = array_intersect($keys, [
            'action',
            'children',
            'html',
            'icon',
            'id',
            'label',
            'label_translated',
            'mode',
            'parameters',
            'permissions',
            'type',
        ]);

        $this->setAttribute('explicit_keys', $keys);

        return $this;
    }

    /**
     * Returns keys that were explicitly set in configuration.
     *
     * @return string[]
     */
    public function explicitKeys()
    {
        return $this->getAttribute('explicit_keys') ?: [];
    }

    /**
     * Returns whether this menu item is active based on the current location.
     *
     * @return bool
     */
    public function isActive()
    {
        switch ($this->type) {

            case MenuPresenceType::GROUP:
                return false;

            case MenuPresenceType::ACTION:
                // Activity is based on named route match, unless specific match is defined
                return false;

            case MenuPresenceType::LINK:
                // Activity is based on URL match, unless specific match is defined
                return false;

            // Default omitted on purpose
        }

        return false;
    }

    /**
     * Removes all children currently on the presence.
     */
    public function clearChildren()
    {
        if ($this->children instanceof Collection) {
            $this->children = new Collection;
            return;
        }

        $this->children = [];
    }

    /**
     * Sets children for this presence.
     *
     * @param Collection|MenuPresenceInterface[] $presences
     */
    public function setChildren($presences)
    {
        $this->children = $presences;
    }

    /**
     * Adds a child to the end of the child list.
     *
     * @param MenuPresenceInterface $presence
     */
    public function addChild(MenuPresenceInterface $presence)
    {
        $children = $this->children ?: [];

        if ($children instanceof Collection) {
            $children->push($presence);
        } else {
            $children[] = $presence;
        }

        $this->children = $children;
    }

    /**
     * Adds a child to the front of the child list.
     *
     * @param MenuPresenceInterface $presence
     */
    public function shiftChild(MenuPresenceInterface $presence)
    {
        $children = $this->children ?: [];

        if ($children instanceof Collection) {
            $children->prepend($presence);
        } else {
            $children = array_prepend($children, $presence);
        }

        $this->children = $children;
    }

}
