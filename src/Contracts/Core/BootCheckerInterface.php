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
     * Returns whether non-API CMS middleware should be registered.
     *
     * @return bool
     */
    public function shouldRegisterCmsWebRoutes();

    /**
     * Returns whether API CMS middleware should be registered.
     *
     * @return bool
     */
    public function shouldRegisterCmsApiRoutes();

    /**
     * Returns whether a CMS web request is running.
     *
     * @return bool
     */
    public function isCmsWebRequest();

    /**
     * Returns whether a CMS API request is running.
     *
     * @return bool
     */
    public function isCmsApiRequest();

}
