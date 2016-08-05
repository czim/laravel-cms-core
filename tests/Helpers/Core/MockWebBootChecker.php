<?php
namespace Czim\CmsCore\Test\Helpers\Core;

use Czim\CmsCore\Contracts\Core\BootCheckerInterface;

/**
 * Class MockWebBootChecker
 *
 * Mocks boot checking for web (non-api) integration tests.
 */
class MockWebBootChecker implements BootCheckerInterface
{

    /**
     * Returns whether the CMS should perform any service providing.
     *
     * @return bool
     */
    public function shouldCmsRegister()
    {
        return true;
    }

    /**
     * Returns whether the CMS should perform service providing for the API specifically.
     *
     * @return bool
     */
    public function shouldCmsApiRegister()
    {
        return true;
    }

    /**
     * Returns whether the CMS should commence booting.
     *
     * @return bool
     */
    public function shouldCmsBoot()
    {
        return true;
    }

    /**
     * Marks the CMS as having fully registered.
     *
     * @param bool $registered
     */
    public function markCmsRegistered($registered = true)
    {
    }

    /**
     * @return bool
     */
    public function isCmsRegistered()
    {
        return true;
    }

    /**
     * Marks the CMS as having fully booted.
     *
     * @param bool $booted
     */
    public function markCmsBooted($booted = true)
    {
    }

    /**
     * @return bool
     */
    public function isCmsBooted()
    {
        return true;
    }

    /**
     * Returns whether the CMS middleware should be registered.
     *
     * @return bool
     */
    public function shouldLoadCmsMiddleware()
    {
        return true;
    }

    /**
     * Returns whether non-API CMS middleware should be registered.
     *
     * @return bool
     */
    public function shouldLoadCmsWebMiddleware()
    {
        return true;
    }

    /**
     * Returns whether API CMS middleware should be registered.
     *
     * @return bool
     */
    public function shouldLoadCmsApiMiddleware()
    {
        return false;
    }

    /**
     * Returns whether non-API CMS middleware should be registered.
     *
     * @return bool
     */
    public function shouldRegisterCmsWebRoutes()
    {
        return true;
    }

    /**
     * Returns whether API CMS middleware should be registered.
     *
     * @return bool
     */
    public function shouldRegisterCmsApiRoutes()
    {
        return false;
    }

    /**
     * Returns whether a CMS web request is running.
     *
     * @return bool
     */
    public function isCmsWebRequest()
    {
        return true;
    }

    /**
     * Returns whether a CMS API request is running.
     *
     * @return bool
     */
    public function isCmsApiRequest()
    {
        return false;
    }
}
