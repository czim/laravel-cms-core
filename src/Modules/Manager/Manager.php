<?php
namespace Czim\CmsCore\Modules\Manager;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Modules\ModuleGeneratorInterface;
use Czim\CmsCore\Contracts\Modules\ModuleInterface;
use Czim\CmsCore\Contracts\Modules\ManagerInterface;
use ReflectionException;

class Manager implements ManagerInterface
{

    /**
     * @var CoreInterface
     */
    protected $core;

    /**
     * Active CMS modules.
     *
     * @var Collection|ModuleInterface[]
     */
    protected $modules;

    /**
     * FQNs of modules or module generators to activate.
     *
     * @var string[]
     */
    protected $moduleClasses;

    /**
     * Module keys keyed by associated class FQN.
     *
     * @var string[]
     */
    protected $associatedClassIndex;


    /**
     * @param CoreInterface $core
     */
    public function __construct(CoreInterface $core)
    {
        $this->core = $core;
    }


    /**
     * Returns module manager version number.
     *
     * @return string
     */
    public function version()
    {
        return '0.0.1';
    }


    /**
     * Starts initialization, collection and registration of modules.
     * This prepares the manager for further requests.
     *
     * @param string[]|null $modules     optional override of config: list of module FQN's
     * @return $this
     */
    public function initialize(array $modules = null)
    {
        if (is_array($modules)) {
            $this->moduleClasses = $modules;
        } else {
            $this->loadConfiguredModuleClasses();
        }

        $this->populateModuleCollection()
             ->sortModules()
             ->populateAssociatedClassIndex();

        return $this;
    }

    /**
     * Loads the module class FQNs from the core config.
     */
    protected function loadConfiguredModuleClasses()
    {
        $this->moduleClasses = $this->core->moduleConfig('modules', []);
    }

    /**
     * Creates module instances for each registered class name
     * and stores them in the module collection.
     *
     * @return $this
     */
    protected function populateModuleCollection()
    {
        $this->modules = new Collection;

        foreach ($this->moduleClasses as $moduleClass) {

            $instance = $this->instantiateClass($moduleClass);

            if ($instance instanceof ModuleGeneratorInterface) {
                $this->storeModulesForGenerator($instance);
                continue;
            }

            // instance is a module
            $this->storeModule($instance);
        }

        return $this;
    }

    /**
     * @param ModuleGeneratorInterface $generator
     */
    protected function storeModulesForGenerator(ModuleGeneratorInterface $generator)
    {
        foreach ($generator->modules() as $module) {
            $this->storeModule($module);
        }
    }

    /**
     * @param ModuleInterface $module
     */
    protected function storeModule(ModuleInterface $module)
    {
        if ($this->modules->has($module->getKey())) {
            throw new \UnexpectedValueException(
                "Module with key '{$module->getKey()}' already registered!"
            );
        }

        $this->modules->put($module->getKey(), $module);
    }

    /**
     * Instantiates a Module or ModuleGenerator instance.
     *
     * @param string $class
     * @return ModuleInterface|ModuleGeneratorInterface
     */
    protected function instantiateClass($class)
    {
        if ( ! class_exists($class)) {
            throw new \InvalidArgumentException("No instantiable class for '{$class}'");
        }

        try {
            $instance = app($class);
        } catch (BindingResolutionException $e) {
            $instance = null;
        } catch (ReflectionException $e) {
            $instance = null;
        }

        if (null === $instance) {
            throw new \InvalidArgumentException(
                "Failed to instantiate Module or ModuleGenerator instance for '{$class}'"
            );
        }

        if (    ! ($instance instanceof ModuleInterface)
            &&  ! ($instance instanceof ModuleGeneratorInterface)
        ) {
            throw new \InvalidArgumentException(
                "Expected ModuleInterface or ModuleGeneratorInterface, got '{$class}'"
            );
        }

        return $instance;
    }

    /**
     * Populates the index by which modules may be looked up by associated class.
     *
     * @return $this
     */
    protected function populateAssociatedClassIndex()
    {
        $this->associatedClassIndex = [];

        foreach ($this->modules as $module) {

            if (    ! $module->getAssociatedClass()
                ||  array_key_exists('', $this->associatedClassIndex)
            ) {
                continue;
            }

            $this->associatedClassIndex[ $module->getAssociatedClass() ] = $module->getKey();
        }

        return $this;
    }

    /**
     * Sorts the modules in an order that will suite the natural defaults
     * for their menu presence.
     *
     * @return $this
     */
    protected function sortModules()
    {
        $this->modules->sortBy(function (ModuleInterface $module) {
            return $module->getName();
        });

        return $this;
    }

    /**
     * Returns all active modules.
     *
     * @return Collection|ModuleInterface[]
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * Returns whether a module with the given key is active.
     *
     * @param string $key
     * @return bool
     */
    public function has($key)
    {
        return $this->modules->has($key);
    }

    /**
     * Returns a module by key.
     *
     * @param string $key
     * @return ModuleInterface|false
     */
    public function get($key)
    {
        if ( ! $this->has($key)) {
            return false;
        }

        return $this->modules->get($key);
    }

    /**
     * Returns a module by its associated class. This may be an
     * Eloquent model, for instance, for modules dedicated to
     * editing a specific class. If multiple associations for the
     * same class exist, the first ordered will be returned.
     *
     * @param string $modelClass FQN of model
     * @return ModuleInterface|false
     */
    public function getByAssociatedClass($modelClass)
    {
        if ( ! array_key_exists($modelClass, $this->associatedClassIndex)) {
            return false;
        }

        $key = $this->associatedClassIndex[ $modelClass ];

        return $this->get($key);
    }

    /**
     * Returns list of service providers for all active modules.
     *
     * @return string[]
     */
    public function getServiceProviders()
    {
        if (empty($this->modules)) {
            return [];
        }

        $providers = [];

        foreach ($this->modules as $module) {
            $providers = array_merge($providers, $module->getServiceProviders());
        }

        return array_unique($providers);
    }

    /**
     * Builds routes for all modules given a router as context.
     *
     * @param Router $router
     */
    public function mapWebRoutes(Router $router)
    {
        foreach ($this->modules as $module) {
            $module->mapWebRoutes($router);
        }
    }

    /**
     * Builds API routes for all modules given a router as context.
     *
     * @param Router $router
     */
    public function mapApiRoutes(Router $router)
    {
        foreach ($this->modules as $module) {
            $module->mapApiRoutes($router);
        }
    }

}
