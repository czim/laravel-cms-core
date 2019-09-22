<?php
namespace Czim\CmsCore\Support\Data\Menu;

use Czim\CmsCore\Contracts\Modules\Data\MenuPresenceInterface;
use Czim\CmsCore\Contracts\Support\Data\MenuConfiguredModulesDataInterface;
use Czim\CmsCore\Support\Data\AbstractDataObject;
use Illuminate\Support\Collection;

/**
 * Class ConfiguredModulesData
 *
 * @property Collection|MenuPresenceInterface[][] $standard
 * @property Collection|MenuPresenceInterface[][] $alternative
 */
class ConfiguredModulesData extends AbstractDataObject implements MenuConfiguredModulesDataInterface
{

    /**
     * Returns list of lists of presences per module key.
     *
     * @return Collection|MenuPresenceInterface[][]
     */
    public function standard(): Collection
    {
        return $this->getAttribute('standard') ?: new Collection;
    }

    /**
     * Returns list of lists of presences per module key.
     *
     * @return Collection|MenuPresenceInterface[][]
     */
    public function alternative(): Collection
    {
        return $this->getAttribute('alternative') ?: new Collection;
    }

}
