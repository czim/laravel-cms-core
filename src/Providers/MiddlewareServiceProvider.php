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

        $this->registerCmsGroupMiddleware()
             ->registerRouteMiddleware();
    }


    public function register()
    {
    }


    /**
     * Registers global middleware for the CMS
     *
     * @return $this
     */
    protected function registerCmsGroupMiddleware()
    {
        $this->router->middlewareGroup($this->getConfiguredGroupName(), []);

        foreach ($this->getGlobalMiddleware() as $middleware) {

            // Don't add if the middleware is already globally enabled in the kernel
            if ($this->kernel->hasMiddleware($middleware)) {
                continue;
            }

            $this->router->pushMiddlewareToGroup($this->getConfiguredGroupName(), $middleware);
        }

        return $this;
    }

    /**
     * Registers route middleware for the CMS
     *
     * @return $this
     */
    protected function registerRouteMiddleware()
    {
        foreach ($this->getRouteMiddleware() as $key => $middleware) {
            $this->router->middleware($key, $middleware);
        }

        return $this;
    }

    /**
     * Returns list of middleware FQNs to load globally.
     *
     * @return string[]
     */
    protected function getGlobalMiddleware()
    {
        return array_filter(
            $this->getConfiguredMiddleware(),
            function () {
                $key = func_get_arg(1);
                return is_integer($key);
            },
            ARRAY_FILTER_USE_BOTH
        );
    }

    /**
     * Returns list of middleware FQNs to load by their key, when defined for routes.
     *
     * @return string[]
     */
    protected function getRouteMiddleware()
    {
        return array_filter(
            $this->getConfiguredMiddleware(),
            function () {
                $key = func_get_arg(1);
                return ! is_integer($key);
            },
            ARRAY_FILTER_USE_BOTH
        );
    }

    /**
     * Returns middleware defined as either key-value pairs or strings.
     *
     * @return string[]
     */
    protected function getConfiguredMiddleware()
    {
        return $this->core->config('middleware.load', []);
    }

    /**
     * Returns the middleware group that all CMS routes belongs to.
     *
     * @return string
     */
    protected function getConfiguredGroupName()
    {
        return $this->core->config('middleware.group', 'cms');
    }

}
