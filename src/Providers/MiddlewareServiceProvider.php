<?php
namespace Czim\CmsCore\Providers;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Czim\CmsCore\Contracts\Core\BootCheckerInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;

class MiddlewareServiceProvider extends ServiceProvider
{

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var Kernel|HttpKernel
     */
    protected $kernel;

    /**
     * @var CoreInterface
     */
    protected $core;


    /**
     * @param Router               $router
     * @param Kernel               $kernel
     * @param CoreInterface        $core
     * @param BootCheckerInterface $bootChecker
     */
    public function boot(Router $router, Kernel $kernel, CoreInterface $core, BootCheckerInterface $bootChecker)
    {
        if (    ! $bootChecker->shouldLoadCmsMiddleware()
            ||  ! ($kernel instanceof HttpKernel)
        ) {
            return;
        }

        $this->router = $router;
        $this->kernel = $kernel;
        $this->core   = $core;

        $this->removeGlobalMiddleware();

        if ($bootChecker->shouldLoadCmsWebMiddleware()) {

            $this->registerCmsWebGroupMiddleware()
                 ->registerWebRouteMiddleware();

        } elseif ($bootChecker->shouldLoadCmsApiMiddleware()) {

            $this->registerCmsApiGroupMiddleware()
                 ->registerApiRouteMiddleware();
        }
    }


    public function register()
    {

    }

    /**
     * Removes all global middleware defined for the App, if possible.
     *
     * Note that this requires the addition of a removeGlobalMiddleware()
     * method on the App\Http\Kernel class.
     *
     * @see \App\Http\Kernel
     *
     * @return $this
     */
    protected function removeGlobalMiddleware()
    {
        if (method_exists($this->kernel, 'removeGlobalMiddleware')) {
            $this->kernel->removeGlobalMiddleware();
        }

        return $this;
    }


    // ------------------------------------------------------------------------------
    //      Web (non-API)
    // ------------------------------------------------------------------------------

    /**
     * Registers global middleware for the CMS
     *
     * @return $this
     */
    protected function registerCmsWebGroupMiddleware()
    {
        $this->router->middlewareGroup($this->getConfiguredWebGroupName(), []);

        foreach ($this->getGlobalWebMiddleware() as $middleware) {

            // Don't add if the middleware is already globally enabled in the kernel
            if ($this->kernel->hasMiddleware($middleware)) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }

            $this->router->pushMiddlewareToGroup($this->getConfiguredWebGroupName(), $middleware);
        }

        return $this;
    }

    /**
     * Registers route middleware for the CMS
     *
     * @return $this
     */
    protected function registerWebRouteMiddleware()
    {
        foreach ($this->getWebRouteMiddleware() as $key => $middleware) {
            $this->router->middleware($key, $middleware);
        }

        return $this;
    }

    /**
     * Returns list of middleware FQNs to load globally.
     *
     * @return string[]
     */
    protected function getGlobalWebMiddleware()
    {
        return $this->filterMiddlewareForGlobal($this->getConfiguredWebMiddleware());
    }

    /**
     * Returns list of middleware FQNs to load by their key, when defined for routes.
     *
     * @return string[]
     */
    protected function getWebRouteMiddleware()
    {
        return $this->filterMiddlewareForRoute($this->getConfiguredWebMiddleware());
    }

    /**
     * Returns middleware defined as either key-value pairs or strings for web requests.
     *
     * @return string[]
     */
    protected function getConfiguredWebMiddleware()
    {
        return $this->core->config('middleware.load', []);
    }

    /**
     * Returns the middleware group that all CMS web routes belongs to.
     *
     * @return string
     */
    protected function getConfiguredWebGroupName()
    {
        return $this->core->config('middleware.group', 'cms');
    }

    // ------------------------------------------------------------------------------
    //      API
    // ------------------------------------------------------------------------------

    /**
     * Registers global middleware for the CMS API.
     *
     * @return $this
     */
    protected function registerCmsApiGroupMiddleware()
    {
        $this->router->middlewareGroup($this->getConfiguredApiGroupName(), []);

        foreach ($this->getGlobalApiMiddleware() as $middleware) {

            // Don't add if the middleware is already globally enabled in the kernel
            if ($this->kernel->hasMiddleware($middleware)) {
                // @codeCoverageIgnoreStart
                continue;
                // @codeCoverageIgnoreEnd
            }

            $this->router->pushMiddlewareToGroup($this->getConfiguredApiGroupName(), $middleware);
        }

        return $this;
    }

    /**
     * Registers route middleware for the CMS API.
     *
     * @return $this
     */
    protected function registerApiRouteMiddleware()
    {
        foreach ($this->getApiRouteMiddleware() as $key => $middleware) {
            $this->router->middleware($key, $middleware);
        }

        return $this;
    }

    /**
     * Returns list of API middleware FQNs to load globally.
     *
     * @return string[]
     */
    protected function getGlobalApiMiddleware()
    {
        return $this->filterMiddlewareForGlobal($this->getConfiguredApiMiddleware());
    }

    /**
     * Returns list of API middleware FQNs to load by their key, when defined for routes.
     *
     * @return string[]
     */
    protected function getApiRouteMiddleware()
    {
        return $this->filterMiddlewareForRoute($this->getConfiguredApiMiddleware());
    }

    /**
     * Returns API middleware defined as either key-value pairs or strings.
     *
     * @return string[]
     */
    protected function getConfiguredApiMiddleware()
    {
        return $this->core->apiConfig('middleware.load', []);
    }

    /**
     * Returns the middleware group that all CMS API routes belongs to.
     *
     * @return string
     */
    protected function getConfiguredApiGroupName()
    {
        return $this->core->apiConfig('middleware.group', 'cms-api');
    }


    /**
     * Filters out any non-global middleware from an array of middleware definitions.
     *
     * @param array $middleware
     * @return array
     */
    protected function filterMiddlewareForGlobal(array $middleware)
    {
        return array_filter(
            $middleware,
            function () {
                $key = func_get_arg(1);
                return is_integer($key);
            },
            ARRAY_FILTER_USE_BOTH
        );
    }

    /**
     * Filters out any non-route middleware from an array of middleware definitions.
     *
     * @param array $middleware
     * @return array
     */
    protected function filterMiddlewareForRoute(array $middleware)
    {
        return array_filter(
            $middleware,
            function () {
                $key = func_get_arg(1);
                return ! is_integer($key);
            },
            ARRAY_FILTER_USE_BOTH
        );
    }
}
