<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Test\Http\Middleware;

use Czim\CmsCore\Contracts\Api\ApiCoreInterface;
use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Contracts\Core\BootCheckerInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Http\Middleware\CheckPermission;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsCore\Test\Helpers\Http\MockRequest;
use Czim\CmsCore\Test\TestCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mockery;
use Mockery\Mock;
use Mockery\MockInterface;

class CheckPermissionTest extends TestCase
{

    /**
     * @test
     */
    function it_passes_through_if_no_permission_is_given()
    {
        /** @var CoreInterface|Mock|MockInterface $coreMock */
        /** @var AuthenticatorInterface|Mock|MockInterface $authMock */
        /** @var Request|Mock|MockInterface $requestMock */
        $coreMock       = Mockery::mock(CoreInterface::class);
        $authMock       = Mockery::mock(AuthenticatorInterface::class);
        $requestMock    = Mockery::mock(Request::class);

        $authMock->shouldReceive('admin')->never();
        $authMock->shouldReceive('can')->never();

        $coreMock->shouldReceive('auth')->andReturn($authMock);

        $middleware = new CheckPermission($coreMock);

        $next = function ($request) {
            return $request;
        };

        static::assertSame($requestMock, $middleware->handle($requestMock, $next));
    }

    /**
     * @test
     */
    function it_passes_through_if_user_is_admin()
    {
        /** @var CoreInterface|Mock|MockInterface $coreMock */
        /** @var AuthenticatorInterface|Mock|MockInterface $authMock */
        /** @var Request|Mock|MockInterface $requestMock */
        $coreMock       = Mockery::mock(CoreInterface::class);
        $authMock       = Mockery::mock(AuthenticatorInterface::class);
        $requestMock    = Mockery::mock(Request::class);

        $authMock->shouldReceive('admin')->atLeast()->once()->andReturn(true);
        $authMock->shouldReceive('can')->never();

        $coreMock->shouldReceive('auth')->andReturn($authMock);

        $middleware = new CheckPermission($coreMock);

        $next = function ($request) {
            return $request;
        };

        static::assertSame($requestMock, $middleware->handle($requestMock, $next, 'test'));
    }

    /**
     * @test
     */
    function it_passes_through_if_user_has_permission()
    {
        /** @var CoreInterface|Mock|MockInterface $coreMock */
        /** @var AuthenticatorInterface|Mock|MockInterface $authMock */
        /** @var Request|Mock|MockInterface $requestMock */
        $coreMock       = Mockery::mock(CoreInterface::class);
        $authMock       = Mockery::mock(AuthenticatorInterface::class);
        $requestMock    = Mockery::mock(Request::class);

        $authMock->shouldReceive('admin')->atLeast()->once()->andReturn(false);
        $authMock->shouldReceive('can')->atLeast()->once()->with('test')->andReturn(true);
        $coreMock->shouldReceive('auth')->andReturn($authMock);

        $middleware = new CheckPermission($coreMock);

        $next = function ($request) {
            return $request;
        };

        static::assertSame($requestMock, $middleware->handle($requestMock, $next, 'test'));
    }

    /**
     * @test
     */
    function it_returns_403_api_response_for_blocked_api_request()
    {
        /** @var AuthenticatorInterface|Mock|MockInterface $authMock */
        /** @var ApiCoreInterface|Mock|MockInterface $apiMock */
        /** @var BootCheckerInterface|Mock|MockInterface $checkerMock */
        /** @var CoreInterface|Mock|MockInterface $coreMock */
        /** @var Request|Mock|MockInterface $requestMock */
        $apiMock     = Mockery::mock(ApiCoreInterface::class);
        $authMock    = Mockery::mock(AuthenticatorInterface::class);
        $checkerMock = Mockery::mock(BootCheckerInterface::class);
        $coreMock    = Mockery::mock(CoreInterface::class);
        $requestMock = Mockery::mock(Request::class);


        $authMock->shouldReceive('admin')->atLeast()->once()->andReturn(false);
        $authMock->shouldReceive('can')->atLeast()->once()->with('test')->andReturn(false);

        $checkerMock->shouldReceive('isCmsApiRequest')->andReturn(true);

        $apiMock->shouldReceive('error')->atLeast()->once()->andReturn('test');

        $coreMock->shouldReceive('api')->andReturn($apiMock);
        $coreMock->shouldReceive('auth')->andReturn($authMock);
        $coreMock->shouldReceive('bootChecker')->andReturn($checkerMock);

        $middleware = new CheckPermission($coreMock);

        $next = function ($request) {
            return $request;
        };

        static::assertEquals('test', $middleware->handle($requestMock, $next, 'test'));
    }

    /**
     * @test
     */
    function it_returns_403_json_response_for_blocked_json_request()
    {
        /** @var AuthenticatorInterface|Mock|MockInterface $authMock */
        /** @var BootCheckerInterface|Mock|MockInterface $checkerMock */
        /** @var CoreInterface|Mock|MockInterface $coreMock */
        /** @var Request|MockRequest|Mock|MockInterface $requestMock */
        $authMock    = Mockery::mock(AuthenticatorInterface::class);
        $checkerMock = Mockery::mock(BootCheckerInterface::class);
        $coreMock    = Mockery::mock(CoreInterface::class);
        $requestMock = Mockery::mock(MockRequest::class);

        $requestMock->shouldReceive('ajax')->once()->andReturn(false);
        $requestMock->shouldReceive('wantsJson')->once()->andReturn(true);

        $authMock->shouldReceive('admin')->atLeast()->once()->andReturn(false);
        $authMock->shouldReceive('can')->atLeast()->once()->with('test')->andReturn(false);

        $checkerMock->shouldReceive('isCmsApiRequest')->andReturn(false);

        $coreMock->shouldReceive('auth')->andReturn($authMock);
        $coreMock->shouldReceive('bootChecker')->andReturn($checkerMock);

        $middleware = new CheckPermission($coreMock);

        $next = function ($request) {
            return $request;
        };

        /** @var Response $response */
        $response = $middleware->handle($requestMock, $next, 'test');

        static::assertInstanceOf(Response::class, $response);
        static::assertEquals(403, $response->getStatusCode());
    }

    /**
     * @test
     */
    function it_returns_a_redirect_response_for_blocked_web_request()
    {
        /** @var AuthenticatorInterface|Mock|MockInterface $authMock */
        /** @var BootCheckerInterface|Mock|MockInterface $checkerMock */
        /** @var CoreInterface|Mock|MockInterface $coreMock */
        /** @var Request|MockRequest|Mock|MockInterface $requestMock */
        $authMock    = Mockery::mock(AuthenticatorInterface::class);
        $checkerMock = Mockery::mock(BootCheckerInterface::class);
        $coreMock    = Mockery::mock(CoreInterface::class);
        $requestMock = Mockery::mock(MockRequest::class);

        $requestMock->shouldReceive('ajax')->andReturn(false);
        $requestMock->shouldReceive('wantsJson')->andReturn(false);

        $authMock->shouldReceive('admin')->atLeast()->once()->andReturn(false);
        $authMock->shouldReceive('can')->atLeast()->once()->with('test')->andReturn(false);

        $checkerMock->shouldReceive('isCmsApiRequest')->andReturn(false);

        $coreMock->shouldReceive('auth')->andReturn($authMock);
        $coreMock->shouldReceive('bootChecker')->andReturn($checkerMock);

        // Register and fake the redirect route
        $this->app['router']->get('testing/ignore', ['as' => 'testing', 'uses' => 'NoController@nowhere']);
        $coreMock->shouldReceive('prefixRoute')->andReturn('testing');

        $this->app->instance(Component::CORE, $coreMock);

        $middleware = new CheckPermission($coreMock);

        $next = function ($request) {
            return $request;
        };

        static::assertInstanceOf(RedirectResponse::class, $middleware->handle($requestMock, $next, 'test'));
    }

}
