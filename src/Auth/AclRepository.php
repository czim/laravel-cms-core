<?php
namespace Czim\CmsCore\Auth;

use Czim\CmsCore\Contracts\Auth\AclRepositoryInterface;
use Czim\CmsCore\Contracts\Modules\Data\AclPresenceInterface;
use Czim\CmsCore\Support\Data\AclPresence;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use UnexpectedValueException;

class AclRepository implements AclRepositoryInterface
{

    /**
     * @var CoreInterface
     */
    protected $core;

    /**
     * Whether the acl data has been prepared, used to prevent
     * performing preparations more than once.
     *
     * @var bool
     */
    protected $prepared = false;

    /**
     * List of all ACL presences.
     *
     * @var Collection|AclPresenceInterface[]
     */
    protected $presences;

    /**
     * List of ACL presences per module.
     *
     * @var Collection|AclPresenceInterface[]   keyed by module key
     */
    protected $modulePresences;

    /**
     * All permissions.
     *
     * @var string[]
     */
    protected $permissions;

    /**
     * Permissions per module.
     *
     * @var string[][]  keyed by module key
     */
    protected $modulePermissions;


    /**
     * @param CoreInterface $core
     */
    public function __construct(CoreInterface $core)
    {
        $this->core = $core;
    }

    /**
     * Prepares the menu for presentation on demand.
     */
    public function initialize(): void
    {
        $this->prepareData();
    }

    /**
     * Retrieves all ACL presences.
     *
     * @return Collection|AclPresenceInterface[]
     */
    public function getAclPresences(): Collection
    {
        $this->prepareData();

        return $this->presences;
    }

    /**
     * Retrieves all ACL presences for a module key.
     *
     * @param string $key
     * @return Collection|AclPresenceInterface[]|null
     */
    public function getAclPresencesByModule(string $key): ?Collection
    {
        $this->prepareData();

        return $this->modulePresences->get($key);
    }

    /**
     * Retrieves a flat list of all permissions.
     *
     * @return string[]
     */
    public function getAllPermissions(): array
    {
        $this->prepareData();

        return $this->permissions;
    }

    /**
     * Retrieves a flat list of all permissions for a module.
     *
     * @param string $key
     * @return string[]
     */
    public function getModulePermissions(string $key): array
    {
        $this->prepareData();

        if ( ! array_key_exists($key, $this->modulePermissions)) {
            return [];
        }

        return $this->modulePermissions[$key];
    }


    // ------------------------------------------------------------------------------
    //      Processing and preparation
    // ------------------------------------------------------------------------------

    /**
     * Prepares CMS data for presentation in the menu views.
     */
    protected function prepareData(): void
    {
        if ($this->prepared) {
            return;
        }

        $this->presences         = new Collection;
        $this->modulePresences   = new Collection;
        $this->permissions       = [];
        $this->modulePermissions = [];

        $this->loadAclModules()
             ->collapsePermissions();

        $this->prepared = true;
    }

    /**
     * Loads the ACL presences from modules and stores it normalized in the main presences collection.
     *
     * @return $this
     */
    protected function loadAclModules(): AclRepositoryInterface
    {
        // Gather all modules with any menu inherent or overridden presence
        // and store them locally according to their nature.

        foreach ($this->core->modules()->getModules() as $moduleKey => $module) {

            $presencesForModule = $module->getAclPresence();

            // If a module has no presence, skip it
            if ( ! $presencesForModule) {
                continue;
            }

            if ( ! $this->modulePresences->has($moduleKey)) {
                $this->modulePresences->put($moduleKey, new Collection);
            }

            foreach ($this->normalizeAclPresence($presencesForModule) as $presence) {

                if ( ! $presence) {
                    // @codeCoverageIgnoreStart
                    continue;
                    // @codeCoverageIgnoreEnd
                }

                $this->presences->push($presence);
                $this->modulePresences->get($moduleKey)->push($presence);
            }
        }

        return $this;
    }

    /**
     * Normalizes ACL presence data to an array of ACLPresence instances.
     *
     * @param mixed $data
     * @return AclPresenceInterface[]
     */
    protected function normalizeAclPresence($data): array
    {
        if ( ! $data) {
            // @codeCoverageIgnoreStart
            return [];
            // @codeCoverageIgnoreEnd
        }

        if ($data instanceof AclPresenceInterface) {

            $data = [$data];

        } elseif (is_array($data) && ! Arr::isAssoc($data)) {

            $presences = [];

            foreach ($data as $nestedData) {

                if (is_array($nestedData)) {
                    $nestedData = new AclPresence($nestedData);
                }

                if ( ! ($nestedData instanceof AclPresenceInterface)) {
                    throw new UnexpectedValueException(
                        'ACL presence data from array provided by module is not an array or ACL presence instance'
                    );
                }

                $presences[] = $nestedData;
            }

            $data = $presences;

        } else {

            $data = [ new AclPresence($data) ];
        }

        /** @var AclPresenceInterface[] $data */

        // Now normalized to an array with one or more ACL presences.
        // Make sure the permission children are listed as simple strings.
        foreach ($data as $index => $presence) {

            $data[ $index ]->setPermissions($presence->permissions());
        }

        return $data;
    }

    /**
     * Collapses all permissions from presences into flat permission arrays.
     *
     * @return $this
     */
    protected function collapsePermissions(): AclRepositoryInterface
    {
        foreach ($this->modulePresences as $moduleKey => $presences) {

            if ( ! array_key_exists($moduleKey, $this->modulePermissions)) {
                $this->modulePermissions[ $moduleKey ] = [];
            }

            /** @var AclPresenceInterface[] $presences */
            foreach ($presences as $presence) {

                if ( ! count($presence->permissions())) {
                    continue;
                }

                $flatPermissions = $presence->permissions();

                $this->permissions                   = array_merge($this->permissions, $flatPermissions);
                $this->modulePermissions[$moduleKey] = array_merge($this->modulePermissions[$moduleKey], $flatPermissions);
            }
        }

        $this->permissions = array_unique($this->permissions);

        return $this;
    }

}
