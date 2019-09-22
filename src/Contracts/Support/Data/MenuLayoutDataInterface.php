<?php
namespace Czim\CmsCore\Contracts\Support\Data;

use Czim\CmsCore\Contracts\Modules\Data\MenuPresenceInterface;
use Czim\DataObject\Contracts\DataObjectInterface;

interface MenuLayoutDataInterface extends DataObjectInterface
{

    /**
     * Returns the layout that should be used for displaying the menu form.
     *
     * @return MenuPresenceInterface[]
     */
    public function layout(): array;

    /**
     * Sets the layout.
     *
     * @param MenuPresenceInterface[] $presences
     * @return $this
     */
    public function setLayout(array $presences): MenuLayoutDataInterface;

    /**
     * Returns the alternative presences.
     *
     * @return MenuPresenceInterface[]
     */
    public function alternative(): array;

    /**
     * Sets the alternative presences.
     *
     * @param MenuPresenceInterface[] $presences
     * @return $this
     */
    public function setAlternative(array $presences): MenuLayoutDataInterface;

}
