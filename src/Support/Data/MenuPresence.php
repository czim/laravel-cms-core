<?php
namespace Czim\CmsCore\Support\Data;

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
 * @property string   $image
 * @property string   $html
 * @property array    $parameters
 * @property array    $children
 */
class MenuPresence extends AbstractDataObject implements MenuPresenceInterface
{
    protected $attributes = [
        'parameters' => [],
        'children'   => [],
    ];

    protected $rules = [
        'id'               => 'string',
        'type'             => 'required|string',
        'label'            => 'string',
        'label_translated' => 'string',
        'action'           => 'string',
        'parameters'       => 'array',
        'image'            => 'string',
        'children'         => 'array',
        'html'             => 'string',
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
     * @return null|array|MenuPresenceInterface[]
     */
    public function children()
    {
        return $this->getAttribute('children');
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
     * Returns icon or image reference for display purposes.
     *
     * @return string
     */
    public function image()
    {
        return $this->getAttribute('image');
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
