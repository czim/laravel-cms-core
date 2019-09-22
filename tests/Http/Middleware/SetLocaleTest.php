<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Test\Http\Middleware;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Support\Localization\LocaleRepositoryInterface;
use Czim\CmsCore\Http\Middleware\SetLocale;
use Czim\CmsCore\Test\Helpers\Http\MockRequest;
use Czim\CmsCore\Test\TestCase;
use Illuminate\Http\Request;

class SetLocaleTest extends TestCase
{

    /**
     * @test
     */
    function it_passes_through_if_cms_is_not_localized()
    {
        /** @var CoreInterface|\PHPUnit_Framework_MockObject_MockObject $coreMock */
        /** @var LocaleRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject $repositoryMock */
        /** @var Request|\PHPUnit_Framework_MockObject_MockObject $requestMock */
        $coreMock       = $this->getMockBuilder(CoreInterface::class)->getMock();
        $repositoryMock = $this->getMockBuilder(LocaleRepositoryInterface::class)->getMock();
        $requestMock    = $this->getMockBuilder(Request::class)->getMock();

        $repositoryMock->expects(static::once())->method('isLocalized')->willReturn(false);
        $repositoryMock->expects(static::never())->method('isAvailable');

        $middleware = new SetLocale($coreMock, $repositoryMock);

        $next = function ($request) { return $request; };

        static::assertSame($requestMock, $middleware->handle($requestMock, $next));
    }

    /**
     * @test
     */
    function it_applies_a_valid_locale_set_in_session()
    {
        /** @var CoreInterface|\PHPUnit_Framework_MockObject_MockObject $coreMock */
        /** @var LocaleRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject $repositoryMock */
        /** @var Request|\PHPUnit_Framework_MockObject_MockObject $requestMock */
        $coreMock       = $this->getMockBuilder(CoreInterface::class)->getMock();
        $repositoryMock = $this->getMockBuilder(LocaleRepositoryInterface::class)->getMock();
        $requestMock    = $this->getMockBuilder(Request::class)->getMock();

        $repositoryMock->expects(static::once())->method('isLocalized')->willReturn(true);
        $repositoryMock->expects(static::once())
            ->method('isAvailable')
            ->with('nl')
            ->willReturn(true);

        $coreMock->method('config')
            ->willReturnCallback(function ($key, $default = null) {
                if ($key === 'session.prefix') {
                    return 'cms::';
                }
                return $default;
            });

        session()->put('cms::locale', 'nl');

        $middleware = new SetLocale($coreMock, $repositoryMock);

        $next = function ($request) { return $request; };

        static::assertSame($requestMock, $middleware->handle($requestMock, $next));

        static::assertEquals('nl', app()->getLocale(), 'Session locale was not set');
    }

    /**
     * @test
     */
    function it_applies_a_valid_locale_set_in_request()
    {
        /** @var CoreInterface|\PHPUnit_Framework_MockObject_MockObject $coreMock */
        /** @var LocaleRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject $repositoryMock */
        /** @var Request|\PHPUnit_Framework_MockObject_MockObject $requestMock */
        $coreMock       = $this->getMockBuilder(CoreInterface::class)->getMock();
        $repositoryMock = $this->getMockBuilder(LocaleRepositoryInterface::class)->getMock();
        $requestMock    = $this->getMockBuilder(MockRequest::class)
            ->setMethods(['getPreferredLanguage'])
            ->getMock();

        $repositoryMock->expects(static::once())->method('isLocalized')->willReturn(true);
        $repositoryMock->method('getDefault')->willReturn('nl');
        $repositoryMock->method('getAvailable')->willReturn(['nl', 'fr']);

        $requestMock->expects(static::once())
            ->method('getPreferredLanguage')
            ->with(static::isType('array'))
            ->willReturn('fr');

        $coreMock->method('config')
            ->willReturnCallback(function ($key, $default = null) {
                switch ($key) {
                    case 'session.prefix':
                        return 'cms::';
                    case 'locale.request-based':
                        return true;
                }
                return $default;
            });

        $middleware = new SetLocale($coreMock, $repositoryMock);

        $next = function ($request) { return $request; };

        static::assertSame($requestMock, $middleware->handle($requestMock, $next));

        static::assertEquals('fr', app()->getLocale(), 'Request locale was not set');
    }

    /**
     * @test
     */
    function it_does_not_apply_invalid_session_locale()
    {
        /** @var CoreInterface|\PHPUnit_Framework_MockObject_MockObject $coreMock */
        /** @var LocaleRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject $repositoryMock */
        /** @var Request|\PHPUnit_Framework_MockObject_MockObject $requestMock */
        $coreMock       = $this->getMockBuilder(CoreInterface::class)->getMock();
        $repositoryMock = $this->getMockBuilder(LocaleRepositoryInterface::class)->getMock();
        $requestMock    = $this->getMockBuilder(Request::class)->getMock();

        $repositoryMock->expects(static::once())->method('isLocalized')->willReturn(true);
        $repositoryMock->expects(static::once())
            ->method('isAvailable')
            ->with('nl')
            ->willReturn(false);

        $coreMock->method('config')
            ->willReturnCallback(function ($key, $default = null) {
                switch ($key) {
                    case 'session.prefix':
                        return 'cms::';
                    case 'locale.request-based':
                        return false;
                }
                return $default;
            });

        session()->put('cms::locale', 'nl');

        $middleware = new SetLocale($coreMock, $repositoryMock);

        $next = function ($request) { return $request; };

        static::assertSame($requestMock, $middleware->handle($requestMock, $next));

        static::assertEquals('en', app()->getLocale(), 'No locale should have been set');
    }

}
