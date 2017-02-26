<?php
namespace Czim\CmsCore\Menu;

use Czim\CmsCore\Contracts\Menu\MenuConfigInterpreterInterface;
use Czim\CmsCore\Contracts\Menu\MenuPermissionsFilterInterface;
use Czim\CmsCore\Contracts\Menu\MenuRepositoryInterface;
use Czim\CmsCore\Contracts\Support\Data\MenuLayoutDataInterface;
use Czim\CmsCore\Contracts\Support\Data\MenuPermissionsIndexDataInterface;
use Illuminate\Support\Collection;
use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Modules\Data\MenuPresenceInterface;

class MenuRepository implements MenuRepositoryInterface
{

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
    }

    /**
     * Attempts to retrieve menu layout from cache.
     *
     * @return MenuLayoutDataInterface|false
     */
    protected function getCachedLayout()
    {
        // todo: get from cache
        return false;
    }

    /**
     * Stores built permissions index in cache.
     *
     * @param MenuLayoutDataInterface $data
     */
    protected function cacheLayout(MenuLayoutDataInterface $data)
    {
        // todo: cache
    }

    /**
     * Attempts to retrieve permissions index from cache.
     *
     * @return MenuPermissionsIndexDataInterface|false
     */
    protected function getCachedPermissionsIndex()
    {
        // todo: get from cache
        return false;
    }

    /**
     * Stores built permissions index in cache.
     *
     * @param MenuPermissionsIndexDataInterface $data
     */
    protected function cachePermissionsIndex(MenuPermissionsIndexDataInterface $data)
    {
        // todo: cache
    }

}
