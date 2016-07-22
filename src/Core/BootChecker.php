<?php
namespace Czim\CmsCore\Core;

use Illuminate\Support\Str;
use Czim\CmsCore\Contracts\Core\BootCheckerInterface;
use Symfony\Component\Console\Input\ArgvInput;

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
     * Returns whether the CMS should perform any service providing.
     *
     * @return bool
     */
    public function shouldCmsRegister()
    {
        if ( ! $this->isCmsEnabled()) {
            return false;
        }

        if ($this->isConsole()) {
            return $this->isCmsEnabledArtisanCommand();
        }

        if ($this->isCmsBeingUnitTested()) {
            return true;
        }

        return $this->isCmsRequest();
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
     * Returns whether the CMS middleware should be registered.
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
     * Returns whether performing a request in the CMS's own context, based on its routing.
     *
     * @return bool
     */
    protected function isCmsRequest()
    {
        if (app()->runningInConsole()) {
            return false;
        }

        // This is only a CMS request if all the prefix segments match the actual route prefixes

        $prefixSegments = $this->splitPrefixSegments($this->getCmsRoutePrefix());
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
        if ( ! $this->isConsole()) {
            return false;
        }

        $command = $this->getArtisanBaseCommand();

        // If no command is given, the CMS will be loaded, to make
        // sure that its commands will appear in the Artisan list.
        if (empty($command)) {
            return true;
        }

        $patterns = $this->getCmsEnabledArtisanPatterns();

        foreach ($patterns as $pattern) {
            if ($command === $pattern || Str::is($pattern, $command)) {
                return true;
            }
        }

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
