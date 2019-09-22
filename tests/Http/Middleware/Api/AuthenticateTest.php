<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Test\Http\Middleware\Api;

use Czim\CmsCore\Contracts\Api\ApiCoreInterface;
use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Http\Middleware\Api\Authenticate;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsCore\Test\Helpers\Http\MockRequest;
use Czim\CmsCore\Test\TestCase;
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
    function it_returns_unauthorized_response_if_not_authenticated_for_ajax_request()
    {
        /** @var ApiCoreInterface|\PHPUnit_Framework_MockObject_MockObject $apiMock */
        /** @var AuthenticatorInterface|\PHPUnit_Framework_MockObject_MockObject $authMock */
        /** @var MockRequest|\PHPUnit_Framework_MockObject_MockObject $requestMock */
        $apiMock     = $this->getMockBuilder(ApiCoreInterface::class)->getMock();
        $authMock    = $this->getMockBuilder(AuthenticatorInterface::class)->getMock();
        $requestMock = $this->getMockBuilder(MockRequest::class)->setMethods(['ajax', 'wantsJson'])->getMock();

        $authMock->expects(static::once())->method('check')->willReturn(false);

        $requestMock->method('ajax')->willReturn(true);
        $requestMock->method('wantsJson')->willReturn(true);

        $apiMock->expects(static::once())
            ->method('response')
            ->with(static::isType('string'), 401)
            ->willReturn('testing-response');

        $this->app->instance(Component::API, $apiMock);

        $middleware = new Authenticate($authMock);

        $next = function ($request) { return $request; };

        /** @var Response $response */
        $response = $middleware->handle($requestMock, $next);

        static::assertEquals('testing-response', $response);
    }

}
