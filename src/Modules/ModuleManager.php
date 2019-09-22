<?php
namespace Czim\CmsCore\Modules;

use Czim\CmsCore\Contracts\Auth\AclRepositoryInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Modules\ModuleGeneratorInterface;
use Czim\CmsCore\Contracts\Modules\ModuleInterface;
use Czim\CmsCore\Contracts\Modules\ModuleManagerInterface;
use InvalidArgumentException;
use ReflectionException;

class ModuleManager implements ModuleManagerInterface
{

    /**
     * The module manager version.
     *
     * @var string
     */
    public const VERSION = '0.0.1';


    /**
     * @var CoreInterface
     */
    protected $core;

    /**
     * @var AclRepositoryInterface
     */
    protected $acl;

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
     * Whether the module manager has been initialized.
     *
     * @var bool
     */
    protected $initialized = false;


    public function __construct(CoreInterface $core, AclRepositoryInterface $acl)
    {
        $this->core = $core;
        $this->acl  = $acl;
    }


    /**
     * Returns module manager version number.
     *
     * @return string
     */
    public function version(): string
    {
        return static::VERSION;
    }


    /**
     * Starts initialization, collection and registration of modules.
     * This prepares the manager for further requests.
     *
     * @param string[]|null $modules     optional override of config: list of module FQN's
     * @return $this
     */
    public function initialize(array $modules = null): ModuleManagerInterface
    {
        if ($this->initialized) {
            return $this;
        }

        if (is_array($modules)) {
            $this->moduleClasses = $modules;
        } else {
            $this->loadConfiguredModuleClasses();
        }

        $this->populateModuleCollection()
             ->sortModules()
             ->populateAssociatedClassIndex();

        $this->initialized = true;

        return $this;
    }

    /**
     * Returns whether the module manager was initialized.
     *
     * @return bool
     */
    public function isInitialized(): bool
    {
        return $this->initialized;
    }

    /**
     * Loads the module class FQNs from the core config.
     */
    protected function loadConfiguredModuleClasses(): void
    {
        $this->moduleClasses = $this->core->moduleConfig('modules', []);
    }

    /**
     * Creates module instances for each registered class name
     * and stores them in the module collection.
     *
     * @return $this
     */
    protected function populateModuleCollection(): ModuleManagerInterface
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
    protected function storeModulesForGenerator(ModuleGeneratorInterface $generator): void
    {
        foreach ($generator->modules() as $module) {
            $this->storeModule($module);
        }
    }

    /**
     * @param ModuleInterface $module
     */
    protected function storeModule(ModuleInterface $module): void
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
        try {
            $instance = app($class);

        } catch (BindingResolutionException $e) {

            throw new InvalidArgumentException(
                "Failed to instantiate Module or ModuleGenerator instance for '{$class}'",
                $e->getCode(),
                $e
            );

        } catch (ReflectionException $e) {

            throw new InvalidArgumentException(
                "Failed to instantiate Module or ModuleGenerator instance for '{$class}'",
                $e->getCode(),
                $e
            );
        }

        if (    ! ($instance instanceof ModuleInterface)
            &&  ! ($instance instanceof ModuleGeneratorInterface)
        ) {
            throw new InvalidArgumentException(
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
    protected function populateAssociatedClassIndex(): ModuleManagerInterface
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
    protected function sortModules(): ModuleManagerInterface
    {
        $this->modules = $this->modules->sortBy(function (ModuleInterface $module) {
            return $module->getName();
        });

        return $this;
    }

    /**
     * Returns all active modules.
     *
     * @return Collection|ModuleInterface[]
     */
    public function getModules(): Collection
    {
        return $this->modules;
    }

    /**
     * Returns whether a module with the given key is active.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->modules->has($key);
    }

    /**
     * Returns a module by key.
     *
     * @param string $key
     * @return ModuleInterface|false
     */
    public function get(string $key)
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
    public function getByAssociatedClass(string $modelClass)
    {
        if ( ! array_key_exists($modelClass, $this->associatedClassIndex)) {
            return false;
        }

        $key = $this->associatedClassIndex[ $modelClass ];

        return $this->get($key);
    }

    /**
     * Builds routes for all modules given a router as context.
     *
     * @param Router $router
     */
    public function mapWebRoutes(Router $router): void
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
    public function mapApiRoutes(Router $router): void
    {
        foreach ($this->modules as $module) {
            $module->mapApiRoutes($router);
        }
    }

    /**
     * Returns all permissions required by loaded modules.
     *
     * @return string[]
     */
    public function getAllPermissions(): array
    {
        $this->acl->initialize();

        return $this->acl->getAllPermissions();
    }

    /**
     * Returns all permissions required by a single loaded module.
     *
     * @param string $key
     * @return string[]
     */
    public function getModulePermissions(string $key):array
    {
        $this->acl->initialize();

        return $this->acl->getModulePermissions($key);
    }

}
