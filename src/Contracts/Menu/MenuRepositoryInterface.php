<?php
namespace Czim\CmsCore\Contracts\Menu;

use Czim\CmsCore\Contracts\Modules\Data\MenuPresenceInterface;
use Illuminate\Support\Collection;

interface MenuRepositoryInterface
{

    /**
     * Sets whether to ignore permissions.
     *
     * @param bool $ignore
     * @return $this
     */
    public function ignorePermission($ignore = true);

    /**
     * Parses available menu presence data, so it may be retrieved.
     */
    public function initialize();

    /**
     * @return Collection|MenuPresenceInterface[]
     */
    public function getMenuLayout();

    /**
     * Retrieves non-standard presences.
     *
     * @return Collection|MenuPresenceInterface[]
     */
    public function getAlternativePresences();

    /**
     * Clears cached menu data.
     */
    public function clearCache();

}
