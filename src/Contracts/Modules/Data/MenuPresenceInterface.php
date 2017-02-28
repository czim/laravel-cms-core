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
     * @return MenuPresenceInterface[]
     */
    public function children();

    /**
     * Returns link or route action, depending on type.
     *
     * @return null|string|array
     */
    public function action();

    /**
     * Returns route parameters for route type.
     *
     * @return array
     */
    public function parameters();

    /**
     * Returns required permissions to see or use the action.
     *
     * @return string[]
     */
    public function permissions();

    /**
     * Returns label for display.
     *
     * @param bool $translated  return translated label if possible
     * @return string
     */
    public function label($translated = true);

    /**
     * Returns the translation key for the label.
     *
     * @return string
     */
    public function translationKey();

    /**
     * Returns icon reference for display purposes.
     *
     * @return string
     */
    public function icon();

    /**
     * Returns icon or image reference for display purposes.
     *
     * @return string
     * @deprecated
     */
    public function image();

    /**
     * Returns configuration mode for menu presence definition.
     *
     * @return string
     */
    public function mode();

    /**
     * Sets keys that are explicitly configured.
     *
     * @param string[] $keys
     * @return $this
     */
    public function setExplicitKeys(array $keys);

    /**
     * Returns keys that were explicitly set in configuration.
     *
     * @return string[]
     */
    public function explicitKeys();

    /**
     * Returns whether this menu item is active based on the current location.
     *
     * @return bool
     */
    public function isActive();

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
     */
    public function addChild(MenuPresenceInterface $presence);

    /**
     * Adds a child to the front of the child list.
     *
     * @param MenuPresenceInterface $presence
     */
    public function shiftChild(MenuPresenceInterface $presence);

}
