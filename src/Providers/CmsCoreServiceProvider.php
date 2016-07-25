<?php
namespace Czim\CmsCore\Providers;

use Czim\CmsCore\Contracts\Api\ApiCoreInterface;
use Czim\CmsCore\Contracts\Menu\MenuRepositoryInterface;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;
use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Contracts\Core\BootCheckerInterface;
use Czim\CmsCore\Contracts\Core\CacheInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Modules\ManagerInterface;
use Czim\CmsCore\Events\CmsHasBooted;
use Czim\CmsCore\Events\CmsHasRegistered;
use Czim\CmsCore\Support\Enums\Component;

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
             ->initializeModuleManager()
             ->registerModuleServiceProviders()
             ->registerConfiguredAliases()
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

        $this->app->bind(CoreInterface::class, Component::CORE);
        $this->app->bind(AuthenticatorInterface::class, Component::AUTH);
        $this->app->bind(ManagerInterface::class, Component::MODULES);
        $this->app->bind(CacheInterface::class, Component::CACHE);
        $this->app->bind(ApiCoreInterface::class, Component::API);
        $this->app->bind(MenuRepositoryInterface::class, Component::MENU);

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
     * Initializes the module manager, after which module information is available.
     *
     * @return $this
     */
    protected function initializeModuleManager()
    {
        $this->getCmsCore()->modules()->initialize();

        return $this;
    }

    /**
     * Registers any service providers listed in the core config.
     *
     * @return $this
     */
    protected function registerConfiguredServiceProviders()
    {
        $providers = $this->getCoreConfig('providers', []);

        foreach ($providers as $provider) {
            $this->app->register($provider);
        }

        return $this;
    }

    /**
     * Registers any service providers defined for modules.
     *
     * @return $this
     */
    protected function registerModuleServiceProviders()
    {
        foreach ($this->getCmsCore()->modules()->getServiceProviders() as $provider) {
            $this->app->register($provider);
        }

        return $this;
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
            return $this;
        }

        $aliasLoader = AliasLoader::getInstance();

        foreach ($aliases as $alias => $binding) {
            $aliasLoader->alias($alias, $binding);
        }

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
     * @return CoreInterface
     */
    protected function getCmsCore()
    {
        return $this->app[Component::CORE];
    }

    /**
     * Returns core CMS configuration array entry.
     * Note that this must be able to function before the Core is bound.
     *
     * @param string $key
     * @param null   $default
     * @return mixed
     */
    protected function getCoreConfig($key, $default = null)
    {
        return array_get($this->app['config']['cms-core'], $key, $default);
    }

}
