<?php
namespace Czim\CmsCore\Menu;

use BadMethodCallException;
use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Menu\MenuConfigInterpreterInterface;
use Czim\CmsCore\Contracts\Menu\MenuPermissionsFilterInterface;
use Czim\CmsCore\Contracts\Menu\MenuRepositoryInterface;
use Czim\CmsCore\Contracts\Modules\Data\MenuPresenceInterface;
use Czim\CmsCore\Contracts\Support\Data\MenuLayoutDataInterface;
use Czim\CmsCore\Contracts\Support\Data\MenuPermissionsIndexDataInterface;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;

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
    public function ignorePermission(bool $ignore = true): MenuRepositoryInterface
    {
        $this->ignorePermission = $ignore;

        return $this;
    }

    /**
     * Prepares the menu for presentation on demand.
     */
    public function initialize(): void
    {
        $this->prepareForPresentation();
    }

    /**
     * @return Collection|MenuPresenceInterface[]
     */
    public function getMenuLayout(): Collection
    {
        return $this->menuLayout;
    }

    /**
     * @return Collection|MenuPresenceInterface[]
     */
    public function getAlternativePresences(): Collection
    {
        return $this->alternativePresences;
    }

    /**
     * Clears cached menu data.
     *
     * @return $this
     */
    public function clearCache(): MenuRepositoryInterface
    {
        $this->getFileSystem()->delete($this->getCachePath());

        return $this;
    }

    /**
     * Writes menu data cache.
     *
     * @return $this
     */
    public function writeCache(): MenuRepositoryInterface
    {
        [$layout, $index] = $this->interpretMenuData();

        $this->getFileSystem()->put(
            $this->getCachePath(),
            $this->serializedInformationForCache($layout, $index)
        );

        return $this;
    }


    // ------------------------------------------------------------------------------
    //      Processing and preparation
    // ------------------------------------------------------------------------------

    /**
     * Prepares CMS data for presentation in the menu views.
     */
    protected function prepareForPresentation(): void
    {
        if ($this->prepared) {
            return;
        }

        if ($this->isMenuCached()) {
            [$layout, $permissionsIndex] = $this->retrieveMenuFromCache();
        } else {
            [$layout, $permissionsIndex] = $this->interpretMenuData();
        }

        if ( ! $this->ignorePermission && ! $this->auth->admin()) {
            $layout = $this->permissionsFilter->filterLayout($layout, $this->auth->user(), $permissionsIndex);
        }

        $this->menuLayout           = new Collection($layout->layout());
        $this->alternativePresences = new Collection($layout->alternative());

        $this->prepared = true;
    }

    /**
     * Interprets and returns menu data.
     *
     * @return array    [ layout, permissionsIndex ]
     */
    protected function interpretMenuData(): array
    {
        $layout = $this->configInterpreter->interpretLayout($this->core->moduleConfig('menu.layout', []));

        return [
            $layout,
            $this->permissionsFilter->buildPermissionsIndex($layout)
        ];
    }


    // ------------------------------------------------------------------------------
    //      Cache
    // ------------------------------------------------------------------------------

    /**
     * Returns whether menu data has been cached.
     *
     * @return bool
     */
    protected function isMenuCached(): bool
    {
        return $this->getFileSystem()->exists($this->getCachePath());
    }

    /**
     * Returns cached menu data: layout and permissions index.
     *
     * @return array    [ layout, permissions index ]
     */
    protected function retrieveMenuFromCache(): array
    {
        if ( ! $this->isMenuCached()) {
            // @codeCoverageIgnoreStart
            throw new BadMethodCallException('Menu was not cached');
            // @codeCoverageIgnoreEnd
        }

        return $this->deserializeInformationFromCache(
            require($this->getCachePath())
        );
    }

    /**
     * Returns the path to which the model information should be cached.
     *
     * @return string
     */
    protected function getCachePath()
    {
        return app()->bootstrapPath() . '/cache/cms_menu.php';
    }

    protected function serializedInformationForCache(
        MenuLayoutDataInterface $layout,
        MenuPermissionsIndexDataInterface $permissionsIndex
    ): string {
        $data = [
            'layout' => serialize($layout),
            'index'  => serialize($permissionsIndex),
        ];

        return '<?php return ' . var_export($data, true) . ';' . PHP_EOL;
    }

    /**
     * @param array $data
     * @return array    [ layout, permissions index ]
     */
    protected function deserializeInformationFromCache(array $data): array
    {
        return [
            unserialize($data['layout']),
            unserialize($data['index']),
        ];
    }

    /**
     * @return Filesystem
     */
    protected function getFileSystem()
    {
        return app('files');
    }

}
