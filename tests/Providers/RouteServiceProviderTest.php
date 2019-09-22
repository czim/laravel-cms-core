<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Test\Providers;

use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Contracts\Core\BootCheckerInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Modules\ModuleManagerInterface;
use Czim\CmsCore\Providers\RouteServiceProvider;
use Czim\CmsCore\Test\TestCase;
use Hamcrest\Matchers;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Mockery;
use Mockery\Mock;
use Mockery\MockInterface;

/**
 * Class RouteServiceProviderTest
 *
 * This has become quite the beast.
 * Probably better to integration-test this, but for now this will serve.
 */
class RouteServiceProviderTest extends TestCase
{

    /**
     * @test
     */
    function it_registers_cms_routes()
    {
        $coreMock    = $this->getMockCore();
        $checkerMock = $this->getMockBootChecker();
        $authMock    = $this->getMockAuth();
        $modulesMock = $this->getMockModules();

        $coreMock->shouldReceive('auth')->atLeast()->once()->andReturn($authMock);
        $coreMock->shouldReceive('modules')->atLeast()->once()->andReturn($modulesMock);

        $checkerMock->shouldReceive('shouldRegisterCmsWebRoutes')
            ->atLeast()->once()
            ->andReturn(true);

        $modulesMock->shouldReceive('mapWebRoutes')->once();

        $provider = new RouteServiceProvider($this->app);
        $provider->boot($this->getElaborateRouterMock(), $coreMock, $checkerMock);
    }

    /**
     * @test
     */
    function it_does_not_register_routes_if_bootchecker_says_not_to()
    {
        $coreMock    = $this->getMockCore();
        $checkerMock = $this->getMockBootChecker();

        $coreMock->shouldReceive('auth')->never();
        $coreMock->shouldReceive('modules')->never();

        $checkerMock->shouldReceive('shouldRegisterCmsWebRoutes')
            ->atLeast()->once()
            ->andReturn(false);

        $provider = new RouteServiceProvider($this->app);
        $provider->boot($this->getMockRouter(), $coreMock, $checkerMock);
    }


    /**
     * @return Router|Mock|MockInterface
     */
    protected function getElaborateRouterMock()
    {
        // The router in the auth closure
        $routerMockAuth = $this->getMockRouter();
        $routerMockAuth->shouldReceive('get')->atLeast()->once();
        $routerMockAuth->shouldReceive('post')->atLeast()->once();

        // The router in the authenticated middleware closure
        $routerMockAuthed = $this->getMockRouter();
        $routerMockAuthed->shouldReceive('get')->atLeast()->once();
        $routerMockAuthed->shouldReceive('post');
        $routerMockAuthed->shouldReceive('group')
            ->once()
            ->with(['prefix' => 'meta'], Matchers::callableValue())
            ->andReturnUsing(function () use ($routerMockAuthed) {
                $callable = func_get_arg(1);
                $callable($routerMockAuthed);
            });

        // The router in the first closure
        $routerMockOne = $this->getMockRouter();
        $routerMockOne->shouldReceive('group')
            ->twice()
            ->with(Matchers::arrayValue(), Matchers::callableValue())
            ->andReturnUsing(function (array $context, callable $callable) use ($routerMockAuth, $routerMockAuthed) {
                // At this level, two groups should be created: the auth group and the main cms group with auth middleware set
                if (Arr::get($context, 'prefix') === 'auth') {
                    $callable($routerMockAuth);
                } else {
                    $callable($routerMockAuthed);
                }
            });

        // Top level router
        $routerMock = $this->getMockRouter();
        $routerMock->shouldReceive('group')
            ->once()
            ->with([
                'prefix'     => 'cms',
                'as'         => 'cms::',
                'middleware' => ['cms'],
            ], Matchers::callableValue())
            ->andReturnUsing(function () use ($routerMockOne) {
                $callable = func_get_arg(1);
                $callable($routerMockOne);
            });

        return $routerMock;
    }

    /**
     * @return AuthenticatorInterface|Mock|MockInterface
     */
    protected function getMockAuth()
    {
        $authMock = Mockery::mock(AuthenticatorInterface::class);

        $authMock->shouldReceive('getRouteLoginAction')
            ->atLeast()->once()
            ->andReturn('login');

        $authMock->shouldReceive('getRouteLoginPostAction')
            ->atLeast()->once()
            ->andReturn('loginPost');

        $authMock->shouldReceive('getRouteLogoutAction')
            ->atLeast()->once()
            ->andReturn('logout');

        $authMock->shouldReceive('getRoutePasswordEmailGetAction')
            ->atLeast()->once()
            ->andReturn('email');

        $authMock->shouldReceive('getRoutePasswordEmailPostAction')
            ->atLeast()->once()
            ->andReturn('loginPost');

        $authMock->shouldReceive('getRoutePasswordResetGetAction')
            ->atLeast()->once()
            ->andReturn('reset');

        $authMock->shouldReceive('getRoutePasswordResetPostAction')
            ->atLeast()->once()
            ->andReturn('resetPost');

        return $authMock;
    }

    /**
     * @return ModuleManagerInterface|Mock|MockInterface
     */
    protected function getMockModules()
    {
        return Mockery::mock(ModuleManagerInterface::class);
    }

    /**
     * @return Router|Mock|MockInterface
     */
    protected function getMockRouter()
    {
        return Mockery::mock(Router::class);
    }

    /**
     * @return CoreInterface|Mock|MockInterface
     */
    protected function getMockCore()
    {
        $mock = Mockery::mock(CoreInterface::class);

        $mock->shouldReceive('config')->andReturnUsing(
            function ($key, $default = null) {
                switch ($key) {

                    case 'middleware.group':
                    case 'route.prefix':
                        return 'cms';

                    case 'route.name-prefix':
                        return 'cms::';

                }
                return $default;
            }
        );

        return $mock;
    }

    /**
     * @return BootCheckerInterface|Mock|MockInterface
     */
    protected function getMockBootChecker()
    {
        return Mockery::mock(BootCheckerInterface::class);
    }

}
