<?php
namespace Czim\CmsCore\Providers\Api;

use Czim\CmsCore\Http\Controllers\Api\ModulesController;
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
                        'middleware' => [
                            CmsMiddleware::API_AUTHENTICATED,
                            CmsMiddleware::API_AUTH_OWNER,
                        ],
                    ],
                    function (Router $router) {

                        $this->buildRoutesForMetaData($router);
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

                $router->{$this->getLoginMethod()}($this->getLoginPath(), $auth->getApiRouteLoginAction());
                $router->{$this->getLogoutMethod()}($this->getLogoutPath(), $auth->getApiRouteLogoutAction());
            }
        );
    }

    /**
     * Builds up routes for accessing meta-data about the CMS and its modules.
     *
     * @param Router $router
     */
    protected function buildRoutesForMetaData(Router $router)
    {
        $router->group(
            [
                'prefix' => 'meta',
            ],
            function (Router $router) {

                // Menu information

                $router->get('menu', [
                    'as'   => 'menu.index',
                    'uses' => $this->core->apiConfig('controllers.menu') . '@index',
                ]);

                // Module information

                $moduleController = $this->core->apiConfig('controllers.modules');

                $router->get('modules', [
                    'as'   => 'modules.index',
                    'uses' => $moduleController . '@index',
                ]);

                $router->get('modules/{key}', [
                    'as'   =>'modules.show',
                    'uses' => $moduleController . '@show',
                ]);

                // CMS version

                $router->get('version', [
                    'as'   => 'version.index',
                    'uses' => $this->core->apiConfig('controllers.version') . '@index',
                ]);
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

    /**
     * @return string
     */
    protected function getLoginPath()
    {
        return $this->core->apiConfig('route.auth.path.login', 'login');
    }

    /**
     * @return string
     */
    protected function getLogoutPath()
    {
        return $this->core->apiConfig('route.auth.path.logout', 'logout');
    }

    /**
     * @return string
     */
    protected function getLoginMethod()
    {
        return $this->core->apiConfig('route.auth.method.login', 'post');
    }

    /**
     * @return string
     */
    protected function getLogoutMethod()
    {
        return $this->core->apiConfig('route.auth.method.logout', 'post');
    }

}
