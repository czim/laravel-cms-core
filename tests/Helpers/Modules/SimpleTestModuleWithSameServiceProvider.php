<?php
namespace Czim\CmsCore\Test\Helpers\Modules;

use Czim\CmsCore\Contracts\Modules\Data\AclPresenceInterface;
use Czim\CmsCore\Contracts\Modules\Data\MenuPresenceInterface;
use Czim\CmsCore\Contracts\Modules\ModuleInterface;
use Illuminate\Routing\Router;

class SimpleTestModuleWithSameServiceProvider implements ModuleInterface
{

    /**
     * Returns unique identifying key for the module.
     * This should also be able to perform as a slug for it.
     *
     * @return string
     */
    public function getKey(): string
    {
        return 'test-module-with-the-same-service-provider';
    }

    /**
     * Returns display name for the module.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'Test Module With Service Providers (2)';
    }

    /**
     * Returns the FQN for a class mainly associated with this module.
     *
     * @return string|null
     */
    public function getAssociatedClass(): ?string
    {
        return null;
    }

    /**
     * Returns a list of FQNs for service providers that should always be registered.
     *
     * @return string[]
     */
    public function getServiceProviders(): array
    {
        return [
            'ServiceProvider\\That\\Does\\Not\\Exist',
        ];
    }

    /**
     * Generates web routes for the module given a contextual router instance.
     * Note that the module is responsible for ACL-checks, including route-based.
     *
     * @param Router $router
     */
    public function mapWebRoutes(Router $router): void
    {
    }

    /**
     * Generates API routes for the module given a contextual router instance.
     * Note that the module is responsible for ACL-checks, including route-based.
     *
     * @param Router $router
     */
    public function mapApiRoutes(Router $router): void
    {
    }

    /**
     * @return null|array|AclPresenceInterface|AclPresenceInterface[]
     */
    public function getAclPresence()
    {
        return null;
    }

    /**
     * Returns data for CMS menu presence.
     *
     * @return null|array|MenuPresenceInterface[]|MenuPresenceInterface[]
     */
    public function getMenuPresence()
    {
        return null;
    }

    /**
     * Returns release or version number of module.
     *
     * @return string
     */
    public function getVersion(): string
    {
        return '1.0.0';
    }

}
