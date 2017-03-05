<?php
namespace Czim\CmsCore\Providers;

use Czim\CmsCore\Contracts\Core\BootCheckerInterface;
use Czim\CmsCore\Http\Controllers\LocaleController;
use Illuminate\Contracts\Foundation\Application as ApplicationContract;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Support\Enums\CmsMiddleware;
use Czim\CmsCore\Support\Enums\NamedRoute;

/**
 * Class RouteServiceProvider
 *
 * Web routes provider. Note that the API routes are handled separately.
 */
class RouteServiceProvider extends ServiceProvider
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
     * @var CoreInterface
     */
    protected $core;


    /**
     * @param Router               $router
     * @param CoreInterface        $core
     * @param BootCheckerInterface $bootChecker
     */
    public function boot(Router $router, CoreInterface $core, BootCheckerInterface $bootChecker)
    {
        // If we don't need the web routes, skip booting entirely
        if ( ! $bootChecker->shouldRegisterCmsWebRoutes()) {
            return;
        }

        $this->router = $router;
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
            // @codeCoverageIgnoreStart
            return $this;
            // @codeCoverageIgnoreEnd
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

                $router->group(
                    [
                        'middleware' => [ CmsMiddleware::AUTHENTICATED ],
                    ],
                    function (Router $router) {

                        $this->buildHomeRoute($router);
                        $this->buildMetaRoutes($router);
                        $this->buildRoutesForModules($router);
                    }
                );
            }
        );

        return $this;
    }

    /**
     * Builds up route for the home page of the CMS.
     *
     * @param Router $router
     */
    protected function buildHomeRoute(Router $router)
    {
        $action = $this->normalizeRouteAction($this->getDefaultHomeAction());

        // Guarantee that the home route has the expected name
        $router->get('/', array_set($action, 'as', NamedRoute::HOME));
    }

    /**
     * Builds up routes for meta functionality of the CMS (such as localization).
     *
     * @param Router $router
     */
    protected function buildMetaRoutes(Router $router)
    {
        $router->group(
            [
                'prefix' => 'meta',
            ],
            function (Router $router) {

                $router->post('set-locale', [
                    'as'   => NamedRoute::LOCALE_SET,
                    'uses' => LocaleController::class . '@setLocale',
                ]);
            }
        );

    }

    /**
     * Builds up routes for authorization in the given router context.
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

                $router->get('login',  $auth->getRouteLoginAction());
                $router->post('login', $auth->getRouteLoginPostAction());
                $router->get('logout', $auth->getRouteLogoutAction());

                $router->get('password/email',          $auth->getRoutePasswordEmailGetAction());
                $router->post('password/email',         $auth->getRoutePasswordEmailPostAction());
                $router->get('password/reset/{token?}', $auth->getRoutePasswordResetGetAction());
                $router->post('password/reset',         $auth->getRoutePasswordResetPostAction());
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
        $this->core->modules()->mapWebRoutes($router);
    }


    /**
     * Normalizes a given string/array route action to array format.
     *
     * @param mixed $action
     * @return array
     */
    protected function normalizeRouteAction($action)
    {
        return is_array($action) ? $action : [ 'uses' => $action ];
    }


    /**
     * @return string
     */
    protected function getCmsPrefix()
    {
        return $this->core->config('route.prefix');
    }

    /**
     * @return string
     */
    protected function getCmsNamePrefix()
    {
        return $this->core->config('route.name-prefix');
    }

    /**
     * @return string
     */
    protected function getCmsMiddlewareGroup()
    {
        return $this->core->config('middleware.group');
    }

    /**
     * Returns the default action to bind to the root /.
     *
     * @return string|array
     */
    protected function getDefaultHomeAction()
    {
        return $this->core->config('route.default');
    }

}
