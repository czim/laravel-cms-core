<?php
namespace Czim\CmsCore\Contracts\Support\Data;

use Czim\CmsCore\Contracts\Modules\Data\MenuPresenceInterface;
use Czim\DataObject\Contracts\DataObjectInterface;
use Illuminate\Support\Collection;

interface MenuConfiguredModulesDataInterface extends DataObjectInterface
{

    /**
     * Returns list of lists of presences per module key.
     *
     * @return Collection|MenuPresenceInterface[][]
     */
    public function standard(): Collection;

    /**
     * Returns list of lists of presences per module key.
     *
     * @return Collection|MenuPresenceInterface[][]
     */
    public function alternative(): Collection;

}
