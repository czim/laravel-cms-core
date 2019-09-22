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
    public function ignorePermission(bool $ignore = true): MenuRepositoryInterface;

    /**
     * Parses available menu presence data, so it may be retrieved.
     */
    public function initialize(): void;

    /**
     * @return Collection|MenuPresenceInterface[]
     */
    public function getMenuLayout(): Collection;

    /**
     * Retrieves non-standard presences.
     *
     * @return Collection|MenuPresenceInterface[]
     */
    public function getAlternativePresences(): Collection;

    /**
     * Clears cached menu data.
     *
     * @return $this
     */
    public function clearCache(): MenuRepositoryInterface;

    /**
     * Writes menu data cache.
     *
     * @return $this
     */
    public function writeCache(): MenuRepositoryInterface;

}
