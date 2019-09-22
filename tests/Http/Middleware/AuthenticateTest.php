<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Test\Http\Middleware;

use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Http\Middleware\Authenticate;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsCore\Test\Helpers\Http\MockRequest;
use Czim\CmsCore\Test\TestCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthenticateTest extends TestCase
{

    /**
     * @test
     */
    function it_passes_through_if_authenticated()
    {
        /** @var AuthenticatorInterface|\PHPUnit_Framework_MockObject_MockObject $authMock */
        /** @var Request|\PHPUnit_Framework_MockObject_MockObject $requestMock */
        $authMock = $this->getMockBuilder(AuthenticatorInterface::class)->getMock();
        $requestMock = $this->getMockBuilder(Request::class)->getMock();

        $authMock->expects(static::once())->method('check')->willReturn(true);

        $middleware = new Authenticate($authMock);

        $next = function ($request) { return $request; };

        static::assertSame($requestMock, $middleware->handle($requestMock, $next));
    }

    /**
     * @test
     */
    function it_redirects_if_not_authenticated_for_non_ajax_request()
    {
        /** @var AuthenticatorInterface|\PHPUnit_Framework_MockObject_MockObject $authMock */
        /** @var MockRequest|\PHPUnit_Framework_MockObject_MockObject $requestMock */
        /** @var CoreInterface|\PHPUnit_Framework_MockObject_MockObject $coreMock */
        $authMock    = $this->getMockBuilder(AuthenticatorInterface::class)->getMock();
        $requestMock = $this->getMockBuilder(MockRequest::class)->setMethods(['ajax', 'wantsJson'])->getMock();
        $coreMock    = $this->getMockBuilder(CoreInterface::class)->getMock();

        $authMock->expects(static::once())->method('check')->willReturn(false);

        $requestMock->method('ajax')->willReturn(false);
        $requestMock->method('wantsJson')->willReturn(false);

        // Register and fake the redirect route
        $this->app['router']->get('testing/ignore', ['as' => 'testing', 'uses' => 'NoController@nowhere']);
        $coreMock->method('prefixRoute')->willReturn('testing');

        $this->app->instance(Component::CORE, $coreMock);

        $middleware = new Authenticate($authMock);

        $next = function ($request) { return $request; };

        static::assertInstanceOf(RedirectResponse::class, $middleware->handle($requestMock, $next));
    }

    /**
     * @test
     */
    function it_returns_unauthorized_response_if_not_authenticated_for_ajax_request()
    {
        /** @var AuthenticatorInterface|\PHPUnit_Framework_MockObject_MockObject $authMock */
        /** @var MockRequest|\PHPUnit_Framework_MockObject_MockObject $requestMock */
        $authMock    = $this->getMockBuilder(AuthenticatorInterface::class)->getMock();
        $requestMock = $this->getMockBuilder(MockRequest::class)->setMethods(['ajax', 'wantsJson'])->getMock();

        $authMock->expects(static::once())->method('check')->willReturn(false);

        $requestMock->method('ajax')->willReturn(true);
        $requestMock->method('wantsJson')->willReturn(true);

        $middleware = new Authenticate($authMock);

        $next = function ($request) { return $request; };

        /** @var Response $response */
        $response = $middleware->handle($requestMock, $next);

        static::assertInstanceOf(Response::class, $response);
        static::assertEquals(401, $response->getStatusCode());
    }

}
