<?php
namespace Czim\CmsCore\Contracts\Modules;

use Illuminate\Routing\Router;
use Illuminate\Support\Collection;

interface ModuleManagerInterface
{

    /**
     * Starts initialization, collection and registration of modules.
     * This prepares the manager for further requests.
     *
     * @param string[]|null $modules     optional override of config: list of module FQN's
     * @return $this
     */
    public function initialize(array $modules = null): ModuleManagerInterface;

    /**
     * Returns whether the module manager was initialized.
     *
     * @return bool
     */
    public function isInitialized(): bool;

    /**
     * Returns whether a module with the given key is active.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool;

    /**
     * Returns a module by key.
     *
     * @param string $key
     * @return ModuleInterface|false
     */
    public function get(string $key);

    /**
     * Returns a module by its associated class. This may be an
     * Eloquent model, for instance, for modules dedicated to
     * editing a specific class. If multiple associations for the
     * same class exist, the first ordered will be returned.
     *
     * @param string $modelClass    FQN of model
     * @return ModuleInterface|false
     */
    public function getByAssociatedClass(string $modelClass);

    /**
     * Returns all active modules.
     *
     * @return Collection|ModuleInterface[]
     */
    public function getModules(): Collection;

    /**
     * Builds web routes for all modules given a router as context.
     *
     * @param Router $router
     */
    public function mapWebRoutes(Router $router): void;

    /**
     * Builds API routes for all modules given a router as context.
     *
     * @param Router $router
     */
    public function mapApiRoutes(Router $router): void;

    /**
     * Returns all permissions required by loaded modules.
     *
     * @return string[]
     */
    public function getAllPermissions(): array;

    /**
     * Returns all permissions required by a single loaded module.
     *
     * @param string $key
     * @return string[]
     */
    public function getModulePermissions(string $key): array;

    /**
     * Returns module manager version number.
     *
     * @return string
     */
    public function version(): string;

}
