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

class CheckPermissionTest extends TestCase
{

    /**
     * @test
     */
    function it_passes_through_if_no_permission_is_given()
    {
        /** @var CoreInterface|\PHPUnit_Framework_MockObject_MockObject $coreMock */
        /** @var AuthenticatorInterface|\PHPUnit_Framework_MockObject_MockObject $authMock */
        /** @var Request|\PHPUnit_Framework_MockObject_MockObject $requestMock */
        $coreMock       = $this->getMockBuilder(CoreInterface::class)->getMock();
        $authMock       = $this->getMockBuilder(AuthenticatorInterface::class)->getMock();
        $requestMock    = $this->getMockBuilder(Request::class)->getMock();

        $authMock->expects(static::never())->method('admin');
        $authMock->expects(static::never())->method('can');

        $coreMock->method('auth')->willReturn($authMock);

        $middleware = new CheckPermission($coreMock);

        $next = function ($request) { return $request; };

        static::assertSame($requestMock, $middleware->handle($requestMock, $next));
    }

    /**
     * @test
     */
    function it_passes_through_if_user_is_admin()
    {
        /** @var CoreInterface|\PHPUnit_Framework_MockObject_MockObject $coreMock */
        /** @var AuthenticatorInterface|\PHPUnit_Framework_MockObject_MockObject $authMock */
        /** @var Request|\PHPUnit_Framework_MockObject_MockObject $requestMock */
        $coreMock       = $this->getMockBuilder(CoreInterface::class)->getMock();
        $authMock       = $this->getMockBuilder(AuthenticatorInterface::class)->getMock();
        $requestMock    = $this->getMockBuilder(Request::class)->getMock();

        $authMock->expects(static::atLeastOnce())->method('admin')->willReturn(true);
        $authMock->expects(static::never())->method('can');

        $coreMock->method('auth')->willReturn($authMock);

        $middleware = new CheckPermission($coreMock);

        $next = function ($request) { return $request; };

        static::assertSame($requestMock, $middleware->handle($requestMock, $next, 'test'));
    }

    /**
     * @test
     */
    function it_passes_through_if_user_has_permission()
    {
        /** @var CoreInterface|\PHPUnit_Framework_MockObject_MockObject $coreMock */
        /** @var AuthenticatorInterface|\PHPUnit_Framework_MockObject_MockObject $authMock */
        /** @var Request|\PHPUnit_Framework_MockObject_MockObject $requestMock */
        $coreMock       = $this->getMockBuilder(CoreInterface::class)->getMock();
        $authMock       = $this->getMockBuilder(AuthenticatorInterface::class)->getMock();
        $requestMock    = $this->getMockBuilder(Request::class)->getMock();

        $authMock->expects(static::atLeastOnce())->method('admin')->willReturn(false);
        $authMock->expects(static::atLeastOnce())->method('can')->with('test')->willReturn(true);

        $coreMock->method('auth')->willReturn($authMock);

        $middleware = new CheckPermission($coreMock);

        $next = function ($request) { return $request; };

        static::assertSame($requestMock, $middleware->handle($requestMock, $next, 'test'));
    }

    /**
     * @test
     */
    function it_returns_403_api_response_for_blocked_api_request()
    {
        /** @var AuthenticatorInterface|\PHPUnit_Framework_MockObject_MockObject $authMock */
        /** @var ApiCoreInterface|\PHPUnit_Framework_MockObject_MockObject $apiMock */
        /** @var BootCheckerInterface|\PHPUnit_Framework_MockObject_MockObject $checkerMock */
        /** @var CoreInterface|\PHPUnit_Framework_MockObject_MockObject $coreMock */
        /** @var Request|\PHPUnit_Framework_MockObject_MockObject $requestMock */
        $apiMock     = $this->getMockBuilder(ApiCoreInterface::class)->getMock();
        $authMock    = $this->getMockBuilder(AuthenticatorInterface::class)->getMock();
        $checkerMock = $this->getMockBuilder(BootCheckerInterface::class)->getMock();
        $coreMock    = $this->getMockBuilder(CoreInterface::class)->getMock();
        $requestMock = $this->getMockBuilder(Request::class)->getMock();


        $authMock->expects(static::atLeastOnce())->method('admin')->willReturn(false);
        $authMock->expects(static::atLeastOnce())->method('can')->with('test')->willReturn(false);

        $checkerMock->method('isCmsApiRequest')->willReturn(true);

        $apiMock->expects(static::atLeastOnce())->method('error')->willReturn('test');

        $coreMock->method('api')->willReturn($apiMock);
        $coreMock->method('auth')->willReturn($authMock);
        $coreMock->method('bootChecker')->willReturn($checkerMock);

        $middleware = new CheckPermission($coreMock);

        $next = function ($request) { return $request; };

        static::assertEquals('test', $middleware->handle($requestMock, $next, 'test'));
    }

    /**
     * @test
     */
    function it_returns_403_json_response_for_blocked_json_request()
    {
        /** @var AuthenticatorInterface|\PHPUnit_Framework_MockObject_MockObject $authMock */
        /** @var BootCheckerInterface|\PHPUnit_Framework_MockObject_MockObject $checkerMock */
        /** @var CoreInterface|\PHPUnit_Framework_MockObject_MockObject $coreMock */
        /** @var Request|MockRequest|\PHPUnit_Framework_MockObject_MockObject $requestMock */
        $authMock    = $this->getMockBuilder(AuthenticatorInterface::class)->getMock();
        $checkerMock = $this->getMockBuilder(BootCheckerInterface::class)->getMock();
        $coreMock    = $this->getMockBuilder(CoreInterface::class)->getMock();

        $requestMock = $this->getMockBuilder(MockRequest::class)
            ->setMethods(['ajax', 'wantsJson'])
            ->getMock();

        $requestMock->expects(static::once())->method('ajax')->willReturn(false);
        $requestMock->expects(static::once())->method('wantsJson')->willReturn(true);

        $authMock->expects(static::atLeastOnce())->method('admin')->willReturn(false);
        $authMock->expects(static::atLeastOnce())->method('can')->with('test')->willReturn(false);

        $checkerMock->method('isCmsApiRequest')->willReturn(false);

        $coreMock->method('auth')->willReturn($authMock);
        $coreMock->method('bootChecker')->willReturn($checkerMock);

        $middleware = new CheckPermission($coreMock);

        $next = function ($request) { return $request; };

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
        /** @var AuthenticatorInterface|\PHPUnit_Framework_MockObject_MockObject $authMock */
        /** @var BootCheckerInterface|\PHPUnit_Framework_MockObject_MockObject $checkerMock */
        /** @var CoreInterface|\PHPUnit_Framework_MockObject_MockObject $coreMock */
        /** @var Request|MockRequest|\PHPUnit_Framework_MockObject_MockObject $requestMock */
        $authMock    = $this->getMockBuilder(AuthenticatorInterface::class)->getMock();
        $checkerMock = $this->getMockBuilder(BootCheckerInterface::class)->getMock();
        $coreMock    = $this->getMockBuilder(CoreInterface::class)->getMock();

        $requestMock = $this->getMockBuilder(MockRequest::class)
            ->setMethods(['ajax', 'wantsJson'])
            ->getMock();

        $requestMock->method('ajax')->willReturn(false);
        $requestMock->method('wantsJson')->willReturn(false);

        $authMock->expects(static::atLeastOnce())->method('admin')->willReturn(false);
        $authMock->expects(static::atLeastOnce())->method('can')->with('test')->willReturn(false);

        $checkerMock->method('isCmsApiRequest')->willReturn(false);

        $coreMock->method('auth')->willReturn($authMock);
        $coreMock->method('bootChecker')->willReturn($checkerMock);

        // Register and fake the redirect route
        $this->app['router']->get('testing/ignore', ['as' => 'testing', 'uses' => 'NoController@nowhere']);
        $coreMock->method('prefixRoute')->willReturn('testing');

        $this->app->instance(Component::CORE, $coreMock);

        $middleware = new CheckPermission($coreMock);

        $next = function ($request) { return $request; };

        static::assertInstanceOf(RedirectResponse::class, $middleware->handle($requestMock, $next, 'test'));
    }

}
