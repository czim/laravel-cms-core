<?php
namespace Czim\CmsCore\Test\Http\Middleware;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Menu\MenuRepositoryInterface;
use Czim\CmsCore\Http\Middleware\InitializeMenu;
use Czim\CmsCore\Test\TestCase;
use Illuminate\Http\Request;

class InitializeMenuTest extends TestCase
{

    /**
     * @test
     */
    function it_initializes_the_menu()
    {
        /** @var CoreInterface|\PHPUnit_Framework_MockObject_MockObject $coreMock */
        /** @var MenuRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject $menuMock */
        /** @var Request|\PHPUnit_Framework_MockObject_MockObject $requestMock */
        $coreMock = $this->getMockBuilder(CoreInterface::class)->getMock();
        $menuMock = $this->getMockBuilder(MenuRepositoryInterface::class)->getMock();
        $requestMock = $this->getMockBuilder(Request::class)->getMock();

        $menuMock->expects(static::once())->method('initialize');

        $coreMock->expects(static::atLeastOnce())->method('menu')->willReturn($menuMock);

        $middleware = new InitializeMenu($coreMock);

        $next = function ($request) { return $request; };

        static::assertSame($requestMock, $middleware->handle($requestMock, $next));
    }

}
