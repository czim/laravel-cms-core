<?php
namespace Czim\CmsCore\Contracts\Support\Data;

use Czim\CmsCore\Contracts\Modules\Data\MenuPresenceInterface;
use Czim\DataObject\Contracts\DataObjectInterface;
use Illuminate\Support\Collection;

interface MenuConfiguredModulesDataInterface extends DataObjectInterface
{

    /**
     * @return Collection|MenuPresenceInterface[]
     */
    public function standard();

    /**
     * @return Collection|MenuPresenceInterface[]
     */
    public function alternative();

}
