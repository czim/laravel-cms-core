<?php
namespace Czim\CmsCore\Contracts\Modules;

use Illuminate\Routing\Router;
use Czim\CmsCore\Contracts\Modules\Data\AclPresenceInterface;
use Czim\CmsCore\Contracts\Modules\Data\MenuPresenceInterface;

interface ModuleInterface
{

    /**
     * Returns unique identifying key for the module.
     * This should also be able to perform as a slug for it.
     *
     * @return string
     */
    public function getKey(): string;

    /**
     * Returns display name for the module.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Returns release or version number of module.
     *
     * @return string|null
     */
    public function getVersion(): ?string;

    /**
     * Returns the FQN for a class mainly associated with this module.
     *
     * @return string|null
     */
    public function getAssociatedClass(): ?string;

    /**
     * Generates web routes for the module given a contextual router instance.
     * Note that the module is responsible for ACL-checks, including route-based.
     *
     * @param Router $router
     */
    public function mapWebRoutes(Router $router): void;

    /**
     * Generates API routes for the module given a contextual router instance.
     * Note that the module is responsible for ACL-checks, including route-based.
     *
     * @param Router $router
     */
    public function mapApiRoutes(Router $router): void;

    /**
     * @return null|array|AclPresenceInterface|AclPresenceInterface[]
     */
    public function getAclPresence();

    /**
     * Returns data for CMS menu presence.
     *
     * @return null|array|MenuPresenceInterface|MenuPresenceInterface[]
     */
    public function getMenuPresence();

}
