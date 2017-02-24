<?php
namespace Czim\CmsCore\Support\Data\Menu;

use Czim\CmsCore\Contracts\Modules\Data\MenuPresenceInterface;
use Czim\CmsCore\Contracts\Support\Data\MenuConfiguredModulesDataInterface;
use Czim\CmsCore\Support\Data\AbstractDataObject;
use Illuminate\Support\Collection;

/**
 * Class ConfiguredModulesData
 *
 * @property Collection|MenuPresenceInterface[] $standard
 * @property Collection|MenuPresenceInterface[] $alternative
 */
class ConfiguredModulesData extends AbstractDataObject implements MenuConfiguredModulesDataInterface
{

    /**
     * @return Collection|MenuPresenceInterface[]
     */
    public function standard()
    {
        return $this->getAttribute('standard') ?: new Collection;
    }

    /**
     * @return Collection|MenuPresenceInterface[]
     */
    public function alternative()
    {
        return $this->getAttribute('alternative') ?: new Collection;
    }

}
