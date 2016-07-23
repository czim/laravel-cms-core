<?php
namespace Czim\CmsCore\Providers\Api;

use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Support\Enums\CmsMiddleware;

/**
 * Class ApiRouteServiceProvider
 *
 * Register this even when the API is not registered, so that the API routes may be cached
 * along with the rest.
 */
class ApiRouteServiceProvider extends ServiceProvider
{

    /**
     * @var ApplicationContract|Application
     */
    protected $app;

    /**
     * @var Router
     */
    protected $router;

    /**
     * @var Kernel
     */
    protected $kernel;

    /**
     * @var CoreInterface
     */
    protected $core;


    /**
     * @param Router        $router
     * @param Kernel        $kernel
     * @param CoreInterface $core
     */
    public function boot(Router $router, Kernel $kernel, CoreInterface $core)
    {
        // If we don't need the web routes, skip booting entirely
        if ( ! $core->bootChecker()->shouldRegisterCmsApiRoutes()) {
            return;
        }

        $this->router = $router;
        $this->kernel = $kernel;
        $this->core   = $core;

        $this->registerRoutes();
    }


    public function register()
    {
    }


    /**
     * Registers routes for the entire CMS.
     *
     * @return $this
     */
    protected function registerRoutes()
    {
        // If the application has all routes cached, skip registering them
        if ($this->app->routesAreCached()) {
            return $this;
        }

        $this->router->group(
            [
                'prefix'     => $this->getCmsPrefix(),
                'as'         => $this->getCmsNamePrefix(),
                'middleware' => [ $this->getCmsMiddlewareGroup() ],
            ],
            function (Router $router) {

                $this->buildRoutesForAuth($router);

                // Embed the routes that require authorization in a group
                // with the middleware to keep guests out.

                $this->router->group(
                    [
                        'middleware' => [ CmsMiddleware::API_AUTHENTICATED ],
                    ],
                    function (Router $router) {

                        $this->buildRoutesForModules($router);
                    }
                );
            }
        );

        return $this;
    }

    /**
     * Builds up routes for API authorization in the given router context.
     *
     * @param Router $router
     */
    protected function buildRoutesForAuth(Router $router)
    {
        $auth = $this->core->auth();

        $router->group(
            [
                'prefix' => 'auth',
            ],
            function (Router $router) use ($auth) {

                $router->post('login', $auth->getApiRouteLoginAction());
                $router->get('logout', $auth->getApiRouteLogoutAction());
            }
        );
    }

    /**
     * Builds up routes for all modules in the given router context.
     *
     * @param Router $router
     */
    protected function buildRoutesForModules(Router $router)
    {
        $this->core->modules()->buildApiRoutes($router);
    }


    /**
     * @return string
     */
    protected function getCmsPrefix()
    {
        return $this->core->apiConfig('route.prefix');
    }

    /**
     * @return string
     */
    protected function getCmsNamePrefix()
    {
        return $this->core->apiConfig('route.name-prefix');
    }

    /**
     * @return string
     */
    protected function getCmsMiddlewareGroup()
    {
        return $this->core->apiConfig('middleware.group');
    }

}
