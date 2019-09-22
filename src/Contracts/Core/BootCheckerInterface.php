<?php
namespace Czim\CmsCore\Contracts\Core;

interface BootCheckerInterface
{

    /**
     * Returns whether the CMS should perform any service providing.
     *
     * @return bool
     */
    public function shouldCmsRegister(): bool;

    /**
     * Returns whether the CMS should perform service providing for the API specifically.
     *
     * @return bool
     */
    public function shouldCmsApiRegister(): bool;

    /**
     * Returns whether the CMS should commence booting.
     *
     * @return bool
     */
    public function shouldCmsBoot(): bool;

    /**
     * Marks the CMS as having fully registered.
     *
     * @param bool $registered
     */
    public function markCmsRegistered(bool $registered = true): void;

    /**
     * @return bool
     */
    public function isCmsRegistered(): bool;

    /**
     * Marks the CMS as having fully booted.
     *
     * @param bool $booted
     */
    public function markCmsBooted(bool $booted = true): void;

    /**
     * @return bool
     */
    public function isCmsBooted(): bool;

    /**
     * Returns whether the CMS middleware should be registered.
     *
     * @return bool
     */
    public function shouldLoadCmsMiddleware(): bool;

    /**
     * Returns whether non-API CMS middleware should be registered.
     *
     * @return bool
     */
    public function shouldLoadCmsWebMiddleware(): bool;

    /**
     * Returns whether API CMS middleware should be registered.
     *
     * @return bool
     */
    public function shouldLoadCmsApiMiddleware(): bool;

    /**
     * Returns whether non-API CMS middleware should be registered.
     *
     * @return bool
     */
    public function shouldRegisterCmsWebRoutes(): bool;

    /**
     * Returns whether API CMS middleware should be registered.
     *
     * @return bool
     */
    public function shouldRegisterCmsApiRoutes(): bool;

    /**
     * Returns whether a CMS web request is running.
     *
     * @return bool
     */
    public function isCmsWebRequest(): bool;

    /**
     * Returns whether a CMS API request is running.
     *
     * @return bool
     */
    public function isCmsApiRequest(): bool;

}
