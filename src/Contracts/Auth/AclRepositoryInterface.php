<?php
namespace Czim\CmsCore\Contracts\Auth;

use Czim\CmsCore\Contracts\Modules\Data\AclPresenceInterface;
use Illuminate\Support\Collection;

interface AclRepositoryInterface
{

    /**
     * Parses available ACL presence data, so it may be retrieved.
     */
    public function initialize(): void;

    /**
     * Retrieves all ACL presences.
     *
     * @return Collection|AclPresenceInterface[]
     */
    public function getAclPresences(): Collection;

    /**
     * Retrieves all ACL presences for a module key.
     *
     * @param string $key
     * @return Collection|AclPresenceInterface[]
     */
    public function getAclPresencesByModule(string $key): ?Collection;

    /**
     * Retrieves a flat list of all permissions.
     *
     * @return string[]
     */
    public function getAllPermissions(): array;

    /**
     * Retrieves a flat list of all permissions for a module.
     *
     * @param string $key
     * @return string[]
     */
    public function getModulePermissions(string $key): array;

}
