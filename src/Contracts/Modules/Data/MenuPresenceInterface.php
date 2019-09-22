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
    public function id(): string;

    /**
     * Returns type of presence.
     *
     * @see \Czim\CmsCore\Support\Enums\MenuPresenceType
     * @return string|null
     */
    public function type(): ?string;

    /**
     * Returns child presences of this presence.
     *
     * @return array|Collection|MenuPresenceInterface[]
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
    public function parameters(): array;

    /**
     * Returns required permissions to see or use the action.
     *
     * @return string[]
     */
    public function permissions(): array;

    /**
     * Returns label for display.
     *
     * @param bool $translated  return translated label if possible
     * @return string|null
     */
    public function label(bool $translated = true): ?string;

    /**
     * Returns the translation key for the label.
     *
     * @return string
     */
    public function translationKey(): ?string;

    /**
     * Returns icon reference for display purposes.
     *
     * @return string
     */
    public function icon(): ?string;

    /**
     * Returns configuration mode for menu presence definition.
     *
     * @return string
     */
    public function mode(): string;

    /**
     * Sets keys that are explicitly configured.
     *
     * @param string[] $keys
     * @return $this
     */
    public function setExplicitKeys(array $keys): MenuPresenceInterface;

    /**
     * Returns keys that were explicitly set in configuration.
     *
     * @return string[]
     */
    public function explicitKeys(): array;

    /**
     * Returns whether this menu item is active based on the current location.
     *
     * @return bool
     */
    public function isActive(): bool;

    /**
     * Removes all children currently on the presence.
     */
    public function clearChildren(): void;

    /**
     * Sets children for this presence.
     *
     * @param array|Collection|MenuPresenceInterface[] $presences
     */
    public function setChildren($presences): void;

    /**
     * Adds a child to the end of the child list.
     *
     * @param MenuPresenceInterface $presence
     */
    public function addChild(MenuPresenceInterface $presence): void;

    /**
     * Adds a child to the front of the child list.
     *
     * @param MenuPresenceInterface $presence
     */
    public function shiftChild(MenuPresenceInterface $presence): void;

}
