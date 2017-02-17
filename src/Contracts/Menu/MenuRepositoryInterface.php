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
     * Retrieves menu presences deliberately grouped.
     *
     * @return Collection|MenuPresenceInterface[]
     */
    public function getMenuGroups();

    /**
     * Retrieves menu presences left ungrouped.
     *
     * @return Collection|MenuPresenceInterface[]
     */
    public function getMenuUngrouped();

    /**
     * Retrieves non-standard presences.
     *
     * @return Collection|MenuPresenceInterface[]
     */
    public function getAlternativePresences();

}
