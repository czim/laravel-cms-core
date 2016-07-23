<?php
namespace Czim\CmsCore\Contracts\Modules;

use Illuminate\Routing\Router;
use Illuminate\Support\Collection;

interface ManagerInterface
{

    /**
     * Starts initialization, collection and registration of modules.
     * This prepares the manager for further requests.
     *
     * @param string[]|null $modules     optional override of config: list of module FQN's
     * @return $this
     */
    public function initialize(array $modules = null);

    /**
     * Returns whether a module with the given key is active.
     *
     * @param string $key
     * @return bool
     */
    public function has($key);

    /**
     * Returns a module by key.
     *
     * @param string $key
     * @return ModuleInterface|false
     */
    public function get($key);

    /**
     * Returns a module by its associated class. This may be an
     * Eloquent model, for instance, for modules dedicated to
     * editing a specific class. If multiple associations for the
     * same class exist, the first ordered will be returned.
     *
     * @param string $modelClass    FQN of model
     * @return ModuleInterface|false
     */
    public function getByAssociatedClass($modelClass);

    /**
     * Returns all active modules.
     *
     * @return Collection|ModuleInterface[]
     */
    public function getModules();

    /**
     * Returns list of service providers for all active modules.
     *
     * @return string[]
     */
    public function getServiceProviders();

    /**
     * Builds web routes for all modules given a router as context.
     *
     * @param Router $router
     */
    public function buildWebRoutes(Router $router);

    /**
     * Builds API routes for all modules given a router as context.
     *
     * @param Router $router
     */
    public function buildApiRoutes(Router $router);

}
