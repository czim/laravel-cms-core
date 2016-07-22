<?php
namespace Czim\CmsCore\Support\Data;

use Czim\DataObject\AbstractDataObject;
use Illuminate\Support\Collection;
use Czim\CmsCore\Contracts\Modules\Data\MenuPresenceInterface;

/**
 * Class MenuPresence
 *
 * Data container that represents the presence of a module in the menu.
 */
class MenuPresence extends AbstractDataObject implements MenuPresenceInterface
{
    protected $attributes = [
        'children' => [],
    ];

    protected $rules = [
        'id'       => 'string',
        'type'     => 'required|string',
        'label'    => 'string',
        'image'    => 'string',
        'children' => 'array',
        'html'     => 'string',
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
     * Returns link or route action, depending on type.
     *
     * @return null|string|array
     */
    public function action()
    {
        return $this->getAttribute('action');
    }

    /**
     * Returns required permissions to see or use the action.
     * Null if no permissions required.
     *
     * @return null|string|string[]
     */
    public function permissions()
    {
        return $this->getAttribute('permissions');
    }

    /**
     * Returns (translatable) label for display.
     *
     * @return string
     */
    public function label()
    {
        return $this->getAttribute('label');
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
     * @return mixed
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
     * @return mixed
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
