<?php
namespace Czim\CmsCore\Contracts\Core;

interface BootCheckerInterface
{

    /**
     * Returns whether the CMS should perform any service providing.
     *
     * @return bool
     */
    public function shouldCmsRegister();

    /**
     * Returns whether the CMS should perform service providing for the API specifically.
     *
     * @return bool
     */
    public function shouldCmsApiRegister();

    /**
     * Returns whether the CMS should commence booting.
     *
     * @return bool
     */
    public function shouldCmsBoot();

    /**
     * Returns whether the CMS middleware should be registered.
     *
     * @return bool
     */
    public function shouldLoadCmsMiddleware();

    /**
     * Returns whether non-API CMS middleware should be registered.
     *
     * @return bool
     */
    public function shouldLoadCmsWebMiddleware();

    /**
     * Returns whether API CMS middleware should be registered.
     *
     * @return bool
     */
    public function shouldLoadCmsApiMiddleware();

    /**
     * Marks the CMS as having fully registered.
     *
     * @param bool $registered
     */
    public function markCmsRegistered($registered = true);

    /**
     * @return bool
     */
    public function isCmsRegistered();

    /**
     * Marks the CMS as having fully booted.
     *
     * @param bool $booted
     */
    public function markCmsBooted($booted = true);

    /**
     * @return bool
     */
    public function isCmsBooted();

}
