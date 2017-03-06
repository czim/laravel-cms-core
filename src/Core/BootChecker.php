<?php
namespace Czim\CmsCore\Core;

use Illuminate\Support\Str;
use Czim\CmsCore\Contracts\Core\BootCheckerInterface;
use Symfony\Component\Console\Input\ArgvInput;

/**
 * Class BootChecker
 *
 * @codeCoverageIgnore  There is no way to reliably test this from within the test environment.
 *                      This class has been extensively manually tested in varying environments.
 */
class BootChecker implements BootCheckerInterface
{

    /**
     * @var bool
     */
    protected $registered = false;

    /**
     * @var bool
     */
    protected $booted = false;

    /**
     * Cached, minor optimization.
     *
     * @var bool|null
     */
    protected $shouldRegister;

    /**
     * Cached, minor optimization.
     *
     * @var bool|null
     */
    protected $registeredConsoleCommand;


    /**
     * Returns whether the CMS should perform any service providing.
     *
     * @return bool
     */
    public function shouldCmsRegister()
    {
        if (null !== $this->shouldRegister) {
            return $this->shouldRegister;
        }

        if ( ! $this->isCmsEnabled()) {
            $this->shouldRegister = false;
            return false;
        }

        if ($this->isConsole()) {
            $this->shouldRegister = $this->isCmsEnabledArtisanCommand();
            return $this->shouldRegister;
        }

        if ($this->isCmsBeingUnitTested()) {
            $this->shouldRegister = true;
            return true;
        }

        $this->shouldRegister = $this->isCmsWebRequest() || $this->isCmsApiRequest();
        return $this->shouldRegister;
    }

    /**
     * Returns whether the CMS should perform service providing for the API specifically.
     *
     * @return bool
     */
    public function shouldCmsApiRegister()
    {
        return $this->shouldCmsRegister()
            && ($this->isCmsApiRequest() || $this->isCmsEnabledArtisanCommand());
    }

    /**
     * Returns whether the CMS should commence booting.
     *
     * @return bool
     */
    public function shouldCmsBoot()
    {
        return $this->registered;
    }


    /**
     * Marks the CMS as having fully registered.
     *
     * @param bool $registered
     */
    public function markCmsRegistered($registered = true)
    {
        $this->registered = (bool) $registered;
    }

    /**
     * @return bool
     */
    public function isCmsRegistered()
    {
        return $this->registered;
    }

    /**
     * Marks the CMS as having fully booted.
     *
     * @param bool $booted
     */
    public function markCmsBooted($booted = true)
    {
        $this->booted = (bool) $booted;
    }

    /**
     * @return bool
     */
    public function isCmsBooted()
    {
        return $this->booted;
    }


    /**
     * Returns whether the CMS middleware should be registered at all.
     *
     * @return bool
     */
    public function shouldLoadCmsMiddleware()
    {
        return  $this->shouldCmsRegister()
            &&  ! $this->isConsole()
            &&  $this->isCmsMiddlewareEnabled();
    }

    /**
     * Returns whether non-API CMS middleware should be registered.
     *
     * @return bool
     */
    public function shouldLoadCmsWebMiddleware()
    {
        return $this->shouldLoadCmsMiddleware() && $this->isCmsWebRequest();
    }

    /**
     * Returns whether API CMS middleware should be registered.
     *
     * @return bool
     */
    public function shouldLoadCmsApiMiddleware()
    {
        return $this->shouldLoadCmsMiddleware() && $this->isCmsApiRequest();
    }

    /**
     * Returns whether non-API CMS middleware should be registered.
     *
     * @return bool
     */
    public function shouldRegisterCmsWebRoutes()
    {
        return $this->isCmsEnabledArtisanCommand() || $this->isCmsWebRequest();
    }

    /**
     * Returns whether API CMS middleware should be registered.
     *
     * @return bool
     */
    public function shouldRegisterCmsApiRoutes()
    {
        return $this->isCmsEnabledArtisanCommand() || $this->isCmsApiRequest();
    }


    /**
     * Returns whether performing a request in the CMS's web context, based on its routing.
     *
     * @return bool
     */
    public function isCmsWebRequest()
    {
        if (app()->runningInConsole()) {
            return false;
        }

        return $this->requestRouteMatchesPrefix($this->getCmsRoutePrefix());
    }

    /**
     * Returns whether performing a request to the CMS API, based on its routing.
     *
     * @return bool
     */
    public function isCmsApiRequest()
    {
        if (app()->runningInConsole() || ! $this->isCmsApiEnabled()) {
            return false;
        }

        return $this->requestRouteMatchesPrefix($this->getCmsApiRoutePrefix());
    }

    /**
     * Returns whether a given route prefix matches the current request.
     *
     * @param string $prefix
     * @return bool
     */
    protected function requestRouteMatchesPrefix($prefix)
    {
        $prefixSegments = $this->splitPrefixSegments($prefix);
        $request = request();

        foreach ($prefixSegments as $index => $segment) {
            if (strtolower($request->segment($index + 1)) !== $segment) {
                return false;
            }
        }

        return true;
    }


    // ------------------------------------------------------------------------------
    //      Command line & Artisan
    // ------------------------------------------------------------------------------

    /**
     * Returns whether currently running in CMS-relevant CLI.
     *
     * @return bool
     */
    protected function isConsole()
    {
        return app()->runningInConsole() && ! app()->runningUnitTests();
    }

    /**
     * Returns whether running an Artisan command that requires full CMS registration.
     *
     * @return bool
     */
    protected function isCmsEnabledArtisanCommand()
    {
        if (null !== $this->registeredConsoleCommand) {
            return $this->registeredConsoleCommand;
        }

        if ( ! $this->isConsole()) {
            $this->registeredConsoleCommand = false;
            return false;
        }

        $command = $this->getArtisanBaseCommand();

        // If no command is given, the CMS will be loaded, to make
        // sure that its commands will appear in the Artisan list.
        if (empty($command)) {
            $this->registeredConsoleCommand = true;
            return true;
        }

        $patterns = $this->getCmsEnabledArtisanPatterns();

        foreach ($patterns as $pattern) {
            if ($command === $pattern || Str::is($pattern, $command)) {
                $this->registeredConsoleCommand = true;
                return true;
            }
        }

        $this->registeredConsoleCommand = false;
        return false;
    }

    /**
     * Returns the base Artisan command, if running one.
     * Should only be called after making sure this is CLI.
     *
     * @return string|null
     */
    protected function getArtisanBaseCommand()
    {
        return strtolower(
            (new ArgvInput())->getFirstArgument()
        );
    }

    /**
     * Returns whether unit tests are currently being run for the CMS specifically.
     *
     * @return bool
     */
    protected function isCmsBeingUnitTested()
    {
       return (bool) config('cms-core.testing', false);
    }


    // ------------------------------------------------------------------------------
    //      Configuration values
    // ------------------------------------------------------------------------------

    /**
     * @return bool
     */
    protected function isCmsEnabled()
    {
        return config('cms-core.enabled', true);
    }

    /**
     * @return bool
     */
    protected function isCmsApiEnabled()
    {
        return config('cms-api.enabled', true);
    }

    /**
     * @return bool
     */
    protected function isCmsMiddlewareEnabled()
    {
        return config('cms-core.middleware.enabled', true);
    }

    /**
     * @return string[]
     */
    protected function getCmsEnabledArtisanPatterns()
    {
        return array_map('strtolower', config('cms-core.artisan.patterns', []));
    }

    /**
     * @return string
     */
    protected function getCmsRoutePrefix()
    {
        return strtolower(config('cms-core.route.prefix'));
    }

    /**
     * @return string
     */
    protected function getCmsApiRoutePrefix()
    {
        return strtolower(config('cms-api.route.prefix'));
    }

    /**
     * Splits a route prefix into segments.
     *
     * @param string $prefix
     * @return string[]
     */
    protected function splitPrefixSegments($prefix)
    {
        return array_filter(explode('/', $prefix));
    }

}
