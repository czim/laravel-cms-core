<?php
namespace Czim\CmsCore\Test\Helpers\Core;

use Czim\CmsCore\Contracts\Core\BootCheckerInterface;

/**
 * Class MockApiBootChecker
 *
 * Mocks boot checking for API integration tests.
 */
class MockApiBootChecker implements BootCheckerInterface
{

    /**
     * Returns whether the CMS should perform any service providing.
     *
     * @return bool
     */
    public function shouldCmsRegister(): bool
    {
        return true;
    }

    /**
     * Returns whether the CMS should perform service providing for the API specifically.
     *
     * @return bool
     */
    public function shouldCmsApiRegister(): bool
    {
        return true;
    }

    /**
     * Returns whether the CMS should commence booting.
     *
     * @return bool
     */
    public function shouldCmsBoot(): bool
    {
        return true;
    }

    /**
     * Marks the CMS as having fully registered.
     *
     * @param bool $registered
     */
    public function markCmsRegistered(bool $registered = true): void
    {
    }

    /**
     * @return bool
     */
    public function isCmsRegistered(): bool
    {
        return true;
    }

    /**
     * Marks the CMS as having fully booted.
     *
     * @param bool $booted
     */
    public function markCmsBooted(bool $booted = true): void
    {
    }

    /**
     * @return bool
     */
    public function isCmsBooted(): bool
    {
        return true;
    }

    /**
     * Returns whether the CMS middleware should be registered.
     *
     * @return bool
     */
    public function shouldLoadCmsMiddleware(): bool
    {
        return true;
    }

    /**
     * Returns whether non-API CMS middleware should be registered.
     *
     * @return bool
     */
    public function shouldLoadCmsWebMiddleware(): bool
    {
        return false;
    }

    /**
     * Returns whether API CMS middleware should be registered.
     *
     * @return bool
     */
    public function shouldLoadCmsApiMiddleware(): bool
    {
        return true;
    }

    /**
     * Returns whether non-API CMS middleware should be registered.
     *
     * @return bool
     */
    public function shouldRegisterCmsWebRoutes(): bool
    {
        return false;
    }

    /**
     * Returns whether API CMS middleware should be registered.
     *
     * @return bool
     */
    public function shouldRegisterCmsApiRoutes(): bool
    {
        return true;
    }

    /**
     * Returns whether a CMS web request is running.
     *
     * @return bool
     */
    public function isCmsWebRequest(): bool
    {
        return false;
    }

    /**
     * Returns whether a CMS API request is running.
     *
     * @return bool
     */
    public function isCmsApiRequest(): bool
    {
        return true;
    }
}
