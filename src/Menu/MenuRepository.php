<?php
namespace Czim\CmsCore\Menu;

use Czim\CmsCore\Contracts\Menu\MenuConfigInterpreterInterface;
use Czim\CmsCore\Contracts\Menu\MenuPermissionsFilterInterface;
use Czim\CmsCore\Contracts\Menu\MenuRepositoryInterface;
use Czim\CmsCore\Contracts\Support\Data\MenuLayoutDataInterface;
use Czim\CmsCore\Contracts\Support\Data\MenuPermissionsIndexDataInterface;
use Exception;
use Illuminate\Support\Collection;
use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Modules\Data\MenuPresenceInterface;

class MenuRepository implements MenuRepositoryInterface
{
    /**
     * The key under which the layout is cached.
     *
     * @var string
     */
    const CACHE_KEY_LAYOUT = 'cms:menu-layout-data';

    /**
     * The key under which the permissions index is cached.
     *
     * @var string
     */
    const CACHE_KEY_INDEX = 'cms:menu-permissions-index';

    /**
     * @var CoreInterface
     */
    protected $core;

    /**
     * @var AuthenticatorInterface
     */
    protected $auth;

    /**
     * @var MenuConfigInterpreterInterface
     */
    protected $configInterpreter;

    /**
     * @var MenuPermissionsFilterInterface
     */
    protected $permissionsFilter;

    /**
     * Whether the menu data has been prepared, used to prevent
     * performing preparations more than once.
     *
     * @var bool
     */
    protected $prepared = false;

    /**
     * Nested structure of menu presences.
     *
     * @var Collection|MenuPresenceInterface[]
     */
    protected $menuLayout;

    /**
     * Alternative presences that do not appear in the normal menu display.
     *
     * @var Collection|MenuPresenceInterface[]
     */
    protected $alternativePresences;

    /**
     * Whether to ignore permissions during initialization.
     *
     * @var bool
     */
    protected $ignorePermission = false;


    /**
     * @param CoreInterface                  $core
     * @param AuthenticatorInterface         $auth
     * @param MenuConfigInterpreterInterface $configInterpreter
     * @param MenuPermissionsFilterInterface $permissionsFilter
     */
    public function __construct(
        CoreInterface $core,
        AuthenticatorInterface $auth,
        MenuConfigInterpreterInterface $configInterpreter,
        MenuPermissionsFilterInterface $permissionsFilter
    ) {
        $this->core              = $core;
        $this->auth              = $auth;
        $this->configInterpreter = $configInterpreter;
        $this->permissionsFilter = $permissionsFilter;

        $this->menuLayout           = new Collection;
        $this->alternativePresences = new Collection;
    }

    /**
     * Sets whether to ignore permissions.
     *
     * @param bool $ignore
     * @return $this
     */
    public function ignorePermission($ignore = true)
    {
        $this->ignorePermission = (bool) $ignore;

        return $this;
    }

    /**
     * Prepares the menu for presentation on demand.
     */
    public function initialize()
    {
        $this->prepareForPresentation();
    }

    /**
     * @return Collection|MenuPresenceInterface[]
     */
    public function getMenuLayout()
    {
        return $this->menuLayout;
    }

    /**
     * @return Collection|MenuPresenceInterface[]
     */
    public function getAlternativePresences()
    {
        return $this->alternativePresences;
    }

    /**
     * Clears cached menu data.
     */
    public function clearCache()
    {
        $this->core->cache()->forget(static::CACHE_KEY_LAYOUT);
        $this->core->cache()->forget(static::CACHE_KEY_INDEX);
    }


    // ------------------------------------------------------------------------------
    //      Processing and preparation
    // ------------------------------------------------------------------------------

    /**
     * Prepares CMS data for presentation in the menu views.
     */
    protected function prepareForPresentation()
    {
        if ($this->prepared) {
            return;
        }

        if ( ! ($layout = $this->getCachedLayout())) {
            $layout = $this->configInterpreter->interpretLayout($this->core->moduleConfig('menu.layout', []));
            $this->cacheLayout($layout);
        }

        if ( ! ($permissionsIndex = $this->getCachedPermissionsIndex())) {
            $permissionsIndex = $this->permissionsFilter->buildPermissionsIndex($layout);
            $this->cachePermissionsIndex($permissionsIndex);
        }


        if ( ! $this->ignorePermission && ! $this->auth->admin()) {
            $layout = $this->permissionsFilter->filterLayout($layout, $this->auth->user(), $permissionsIndex);
        }

        $this->menuLayout           = new Collection($layout->layout());
        $this->alternativePresences = new Collection($layout->alternative());

        $this->prepared = true;
    }

    /**
     * Returns whether the CMS is configured to cache menu data.
     *
     * @return bool
     */
    protected function isCacheEnabled()
    {
        return (bool) $this->core->moduleConfig('menu.cache');
    }

    /**
     * Attempts to retrieve menu layout from cache.
     *
     * @return MenuLayoutDataInterface|false
     */
    protected function getCachedLayout()
    {
        if ( ! $this->isCacheEnabled() || ! $this->core->cache()->has(static::CACHE_KEY_LAYOUT)) {
            return false;
        }

        try {
            $layout = $this->core->cache()->get(static::CACHE_KEY_LAYOUT);

        } catch (Exception $e) {

            $this->clearCache();
            return false;
        }

        if ( ! ($layout instanceof MenuLayoutDataInterface)) {
            return false;
        }

        return $layout;
    }

    /**
     * Stores built permissions index in cache.
     *
     * @param MenuLayoutDataInterface $data
     */
    protected function cacheLayout(MenuLayoutDataInterface $data)
    {
        if ( ! $this->isCacheEnabled()) {
            return;
        }

        $this->core->cache()->forever(static::CACHE_KEY_LAYOUT, $data);
    }

    /**
     * Attempts to retrieve permissions index from cache.
     *
     * @return MenuPermissionsIndexDataInterface|false
     */
    protected function getCachedPermissionsIndex()
    {
        if ( ! $this->isCacheEnabled() || ! $this->core->cache()->has(static::CACHE_KEY_INDEX)) {
            return false;
        }

        try {
            $index = $this->core->cache()->get(static::CACHE_KEY_INDEX);

        } catch (Exception $e) {

            $this->clearCache();
            return false;
        }

        if ( ! ($index instanceof MenuPermissionsIndexDataInterface)) {
            return false;
        }

        return $index;
    }

    /**
     * Stores built permissions index in cache.
     *
     * @param MenuPermissionsIndexDataInterface $data
     */
    protected function cachePermissionsIndex(MenuPermissionsIndexDataInterface $data)
    {
        if ( ! $this->isCacheEnabled()) {
            return;
        }

        $this->core->cache()->forever(static::CACHE_KEY_INDEX, $data);
    }

}
