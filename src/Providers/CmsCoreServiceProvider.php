<?php
namespace Czim\CmsCore\Providers;

use Czim\CmsCore\Console\Commands;
use Czim\CmsCore\Contracts\Api\ApiCoreInterface;
use Czim\CmsCore\Contracts\Auth\AclRepositoryInterface;
use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Contracts\Core\BootCheckerInterface;
use Czim\CmsCore\Contracts\Core\CacheInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Core\NotifierInterface;
use Czim\CmsCore\Contracts\Menu\MenuConfigInterpreterInterface;
use Czim\CmsCore\Contracts\Menu\MenuModulesInterpreterInterface;
use Czim\CmsCore\Contracts\Menu\MenuPermissionsFilterInterface;
use Czim\CmsCore\Contracts\Menu\MenuRepositoryInterface;
use Czim\CmsCore\Contracts\Modules\ModuleManagerInterface;
use Czim\CmsCore\Contracts\Support\Localization\LocaleRepositoryInterface;
use Czim\CmsCore\Contracts\Support\View\AssetManagerInterface;
use Czim\CmsCore\Events\CmsHasBooted;
use Czim\CmsCore\Events\CmsHasRegistered;
use Czim\CmsCore\Menu\MenuConfigInterpreter;
use Czim\CmsCore\Menu\MenuModulesInterpreter;
use Czim\CmsCore\Menu\MenuPermissionsFilter;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsCore\Support\Localization\LocaleRepository;
use Czim\CmsCore\Support\View\AssetManager;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class CmsCoreServiceProvider extends ServiceProvider
{

    public function boot()
    {
        if ( ! $this->shouldCmsBoot()) return;

        $this->bootConfig()
             ->finalizeBoot();
    }


    public function register()
    {
        $this->registerConfig()
             ->registerBootChecker();

        if ( ! $this->shouldCmsRegister()) return;

        $this->registerCoreComponents()
             ->registerExceptionHandler()
             ->registerConfiguredServiceProviders()
             ->registerConfiguredAliases()
             ->registerInterfaceBindings()
             ->registerConsoleCommands()
             ->finalizeRegistration();
    }


    // ------------------------------------------------------------------------------
    //      Registration
    // ------------------------------------------------------------------------------

    /**
     * @return $this
     */
    protected function registerConfig()
    {
        $this->mergeConfigFrom(
            realpath(dirname(__DIR__) . '/../config/cms-core.php'),
            'cms-core'
        );

        $this->mergeConfigFrom(
            realpath(dirname(__DIR__) . '/../config/cms-modules.php'),
            'cms-modules'
        );

        $this->mergeConfigFrom(
            realpath(dirname(__DIR__) . '/../config/cms-api.php'),
            'cms-api'
        );

        return $this;
    }

    /**
     * Registers required checker to facilitate determining whether
     * the CMS should be registered or booted.
     *
     * @return $this
     */
    protected function registerBootChecker()
    {
        $this->app->singleton(Component::BOOTCHECKER, $this->getCoreConfig('bindings.' . Component::BOOTCHECKER));

        $this->app->bind(BootCheckerInterface::class, Component::BOOTCHECKER);

        return $this;
    }

    /**
     * @return bool
     */
    protected function shouldCmsRegister()
    {
        return $this->getBootChecker()->shouldCmsRegister();
    }

    /**
     * Registers core components for the CMS.
     *
     * @return $this
     */
    protected function registerCoreComponents()
    {
        $this->app->singleton(Component::CORE, $this->getCoreConfig('bindings.' . Component::CORE));
        $this->app->singleton(Component::AUTH, $this->getCoreConfig('bindings.' . Component::AUTH));
        $this->app->singleton(Component::MODULES, $this->getCoreConfig('bindings.' . Component::MODULES));
        $this->app->singleton(Component::CACHE, $this->getCoreConfig('bindings.' . Component::CACHE));
        $this->app->singleton(Component::API, $this->getCoreConfig('bindings.' . Component::API));
        $this->app->singleton(Component::MENU, $this->getCoreConfig('bindings.' . Component::MENU));
        $this->app->singleton(Component::ACL, $this->getCoreConfig('bindings.' . Component::ACL));
        $this->app->singleton(Component::NOTIFIER, $this->getCoreConfig('bindings.' . Component::NOTIFIER));
        $this->app->singleton(Component::ASSETS, $this->getCoreConfig('bindings.' . Component::ASSETS, AssetManager::class));

        $this->app->bind(CoreInterface::class, Component::CORE);
        $this->app->bind(AuthenticatorInterface::class, Component::AUTH);
        $this->app->bind(ModuleManagerInterface::class, Component::MODULES);
        $this->app->bind(CacheInterface::class, Component::CACHE);
        $this->app->bind(ApiCoreInterface::class, Component::API);
        $this->app->bind(MenuRepositoryInterface::class, Component::MENU);
        $this->app->bind(AclRepositoryInterface::class, Component::ACL);
        $this->app->bind(NotifierInterface::class, Component::NOTIFIER);
        $this->app->bind(AssetManagerInterface::class, Component::ASSETS);

        return $this;
    }

    /**
     * Registers the CMS's exception handler, replacing the app's handler.
     *
     * @return $this
     */
    protected function registerExceptionHandler()
    {
        $this->app->bind(ExceptionHandler::class, $this->getCoreConfig('exceptions.handler'));

        return $this;
    }

    /**
     * Registers any service providers listed in the core config.
     *
     * @return $this
     */
    protected function registerConfiguredServiceProviders()
    {
        $providers = $this->collectConfiguredServiceProviders();

        foreach ($providers as $provider) {
            $this->app->register($provider);
        }

        return $this;
    }

    /**
     * Collects an ordered list of service providers defined by core and modules configuration.
     *
     * @return string[]
     */
    protected function collectConfiguredServiceProviders()
    {
        return array_merge(
            $this->getCoreConfig('providers', []),
            $this->getModulesConfig('providers', []),
            $this->getCoreConfig('providers-after', [])
        );
    }

    /**
     * Registers any aliases defined in the configuration.
     *
     * @return $this
     */
    protected function registerConfiguredAliases()
    {
        $aliases = $this->getCoreConfig('aliases', []);

        if (empty($aliases)) {
            // @codeCoverageIgnoreStart
            return $this;
            // @codeCoverageIgnoreEnd
        }

        $aliasLoader = AliasLoader::getInstance();

        foreach ($aliases as $alias => $binding) {
            $aliasLoader->alias($alias, $binding);
        }

        return $this;
    }

    /**
     * Registers standard interface bindings.
     *
     * @return $this
     */
    protected function registerInterfaceBindings()
    {
        $this->app->singleton(LocaleRepositoryInterface::class, LocaleRepository::class);
        $this->app->singleton(MenuConfigInterpreterInterface::class, MenuConfigInterpreter::class);
        $this->app->singleton(MenuModulesInterpreterInterface::class, MenuModulesInterpreter::class);
        $this->app->singleton(MenuPermissionsFilterInterface::class, MenuPermissionsFilter::class);

        return $this;
    }

    /**
     * Performs final registration tasks.
     */
    protected function finalizeRegistration()
    {
        $this->getBootChecker()->markCmsRegistered();

        event(new CmsHasRegistered);
    }



    // ------------------------------------------------------------------------------
    //      Booting
    // ------------------------------------------------------------------------------

    /**
     * @return bool
     */
    protected function shouldCmsBoot()
    {
        return $this->getBootChecker()->shouldCmsBoot();
    }


    /**
     * @return $this
     */
    protected function bootConfig()
    {
        $this->publishes([
            realpath(dirname(__DIR__) . '/../config/cms-core.php') => config_path('cms-core.php'),
        ]);

        $this->publishes([
            realpath(dirname(__DIR__) . '/../config/cms-modules.php') => config_path('cms-modules.php'),
        ]);

        $this->publishes([
            realpath(dirname(__DIR__) . '/../config/cms-api.php') => config_path('cms-api.php'),
        ]);

        return $this;
    }

    /**
     * Register CMS console commands
     *
     * @return $this
     */
    protected function registerConsoleCommands()
    {
        $this->app->singleton('cms.commands.core-menu-show', Commands\ShowMenu::class);
        $this->app->singleton('cms.commands.core-menu-cache', Commands\CacheMenu::class);
        $this->app->singleton('cms.commands.core-menu-clear', Commands\ClearMenuCache::class);
        $this->app->singleton('cms.commands.core-modules-show', Commands\ShowModules::class);

        $this->commands([
            'cms.commands.core-menu-show',
            'cms.commands.core-menu-cache',
            'cms.commands.core-menu-clear',
            'cms.commands.core-modules-show',
        ]);

        return $this;
    }

    /**
     * Performs final booting tasks.
     */
    protected function finalizeBoot()
    {
        $this->getBootChecker()->markCmsBooted();

        event(new CmsHasBooted);
    }


    // ------------------------------------------------------------------------------
    //      Getters for bound services
    // ------------------------------------------------------------------------------

    /**
     * @return BootCheckerInterface
     */
    protected function getBootChecker()
    {
        return $this->app[Component::BOOTCHECKER];
    }

    /**
     * Returns core CMS configuration array entry.
     * Note that this must be able to function before the Core is bound.
     *
     * @param string     $key
     * @param mixed|null $default
     * @return mixed
     */
    protected function getCoreConfig($key, $default = null)
    {
        return array_get($this->app['config']['cms-core'], $key, $default);
    }

    /**
     * Returns modules CMS configuration array entry.
     * Note that this must be able to function before the Core is bound.
     *
     * @param string     $key
     * @param mixed|null $default
     * @return mixed
     */
    protected function getModulesConfig($key, $default = null)
    {
        return array_get($this->app['config']['cms-modules'], $key, $default);
    }

}
