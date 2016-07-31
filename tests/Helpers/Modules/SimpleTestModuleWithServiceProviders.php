<?php
namespace Czim\CmsCore\Test\Helpers\Modules;

use Czim\CmsCore\Contracts\Modules\Data\AclPresenceInterface;
use Czim\CmsCore\Contracts\Modules\Data\MenuPresenceInterface;
use Czim\CmsCore\Contracts\Modules\ModuleInterface;
use Czim\CmsCore\Modules\Manager\Manager;
use Illuminate\Routing\Router;

class SimpleTestModuleWithServiceProviders implements ModuleInterface
{

    /**
     * Returns unique identifying key for the module.
     * This should also be able to perform as a slug for it.
     *
     * @return string
     */
    public function getKey()
    {
        return 'test-module-with-service-providers';
    }

    /**
     * Returns display name for the module.
     *
     * @return string
     */
    public function getName()
    {
        return 'Test Module With Service Providers';
    }

    /**
     * Returns the FQN for a class mainly associated with this module.
     *
     * @return string|null
     */
    public function getAssociatedClass()
    {
        return Manager::class;
    }

    /**
     * Returns a list of FQNs for service providers that should always be registered.
     *
     * @return string[]
     */
    public function getServiceProviders()
    {
        return [
            'ServiceProvider\\That\\Does\\Not\\Exist',
            'Another\\Registered\\ServiceProvider',
        ];
    }

    /**
     * Generates web routes for the module given a contextual router instance.
     * Note that the module is responsible for ACL-checks, including route-based.
     *
     * @param Router $router
     */
    public function mapWebRoutes(Router $router)
    {
    }

    /**
     * Generates API routes for the module given a contextual router instance.
     * Note that the module is responsible for ACL-checks, including route-based.
     *
     * @param Router $router
     */
    public function mapApiRoutes(Router $router)
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
     * @return string|null
     */
    public function getVersion()
    {
        return '1.0.0';
    }

}
