<?php
namespace Czim\CmsCore\Test\Http\Middleware;

use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Http\Middleware\RedirectIfAuthenticated;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsCore\Test\TestCase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RedirectIfAuthenticatedTest extends TestCase
{

    /**
     * @test
     */
    function it_passes_through_if_not_authenticated()
    {
        /** @var AuthenticatorInterface|\PHPUnit_Framework_MockObject_MockObject $authMock */
        /** @var Request|\PHPUnit_Framework_MockObject_MockObject $requestMock */
        $authMock    = $this->getMockBuilder(AuthenticatorInterface::class)->getMock();
        $requestMock = $this->getMockBuilder(Request::class)->getMock();

        $authMock->expects(static::once())->method('check')->willReturn(false);

        $middleware = new RedirectIfAuthenticated($authMock);

        $next = function ($request) { return $request; };

        static::assertSame($requestMock, $middleware->handle($requestMock, $next));
    }

    /**
     * @test
     */
    function it_redirects_if_authenticated_for_non_ajax_request()
    {
        /** @var AuthenticatorInterface|\PHPUnit_Framework_MockObject_MockObject $authMock */
        /** @var Request|\PHPUnit_Framework_MockObject_MockObject $requestMock */
        $authMock = $this->getMockBuilder(AuthenticatorInterface::class)->getMock();
        $requestMock = $this->getMockBuilder(Request::class)->getMock();
        $coreMock = $this->getMockBuilder(CoreInterface::class)->getMock();

        $authMock->expects(static::once())->method('check')->willReturn(true);

        // Register and fake the redirect route
        $this->app['router']->get('testing/ignore', ['as' => 'testing', 'uses' => 'NoController@nowhere']);
        $coreMock->method('prefixRoute')->willReturn('testing');

        $this->app->instance(Component::CORE, $coreMock);

        $middleware = new RedirectIfAuthenticated($authMock);

        $next = function ($request) { return $request; };

        static::assertInstanceOf(RedirectResponse::class, $middleware->handle($requestMock, $next));
    }

}
