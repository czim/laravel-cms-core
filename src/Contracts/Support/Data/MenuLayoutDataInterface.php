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
     * @return MenuPresenceInterface[]
     */
    public function alternative();

}
