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
    public function layout();

    /**
     * Sets the layout.
     *
     * @param MenuPresenceInterface[] $presences
     * @return $this
     */
    public function setLayout(array $presences);

    /**
     * Returns the alternative presences.
     *
     * @return MenuPresenceInterface[]
     */
    public function alternative();

    /**
     * Sets the alternative presences.
     *
     * @param MenuPresenceInterface[] $presences
     * @return $this
     */
    public function setAlternative(array $presences);

}
