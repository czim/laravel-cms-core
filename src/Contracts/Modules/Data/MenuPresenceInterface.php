<?php
namespace Czim\CmsCore\Contracts\Modules\Data;

use Czim\DataObject\Contracts\DataObjectInterface;
use Illuminate\Support\Collection;

interface MenuPresenceInterface extends DataObjectInterface
{

    /**
     * Returns unique identifier for the menu presence.
     *
     * @return string
     */
    public function id();

    /**
     * Returns type of presence.
     *
     * @see \Czim\CmsCore\Support\Enums\MenuPresenceType
     * @return string
     */
    public function type();

    /**
     * Returns child presences of this presence.
     *
     * @return null|array|MenuPresenceInterface[]
     */
    public function children();

    /**
     * Returns link or route action, depending on type.
     *
     * @return null|string|array
     */
    public function action();

    /**
     * Returns required permissions to see or use the action.
     * Null if no permissions required.
     *
     * @return null|string|string[]
     */
    public function permissions();

    /**
     * Returns (translatable) label for display.
     *
     * @return string
     */
    public function label();

    /**
     * Returns icon or image reference for display purposes.
     *
     * @return string
     */
    public function image();

    /**
     * Removes all children currently on the presence.
     */
    public function clearChildren();

    /**
     * Sets children for this presence.
     *
     * @param Collection|MenuPresenceInterface[] $presences
     */
    public function setChildren($presences);

    /**
     * Adds a child to the end of the child list.
     *
     * @param MenuPresenceInterface $presence
     * @return mixed
     */
    public function addChild(MenuPresenceInterface $presence);

    /**
     * Adds a child to the front of the child list.
     *
     * @param MenuPresenceInterface $presence
     * @return mixed
     */
    public function shiftChild(MenuPresenceInterface $presence);

}
