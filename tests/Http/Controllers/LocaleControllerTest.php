<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Test\Http\Controllers;

use Czim\CmsCore\Contracts\Support\Localization\LocaleRepositoryInterface;
use Czim\CmsCore\Http\Controllers\LocaleController;
use Czim\CmsCore\Http\Requests\SetLocaleRequest;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsCore\Test\CmsBootTestCase;
use Illuminate\Http\RedirectResponse;

class LocaleControllerTest extends CmsBootTestCase
{

    /**
     * @test
     */
    function it_sets_an_available_locale()
    {
        /** @var LocaleRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject $repositoryMock */
        /** @var SetLocaleRequest|\PHPUnit_Framework_MockObject_MockObject $requestMock */
        $repositoryMock = $this->getMockBuilder(LocaleRepositoryInterface::class)->getMock();
        $requestMock    = $this->getMockBuilder(SetLocaleRequest::class)->getMock();

        $repositoryMock->expects(static::atLeastOnce())
            ->method('isAvailable')
            ->with('nl')
            ->willReturn(true);

        $requestMock->expects(static::atLeastOnce())
            ->method('input')
            ->with('locale')
            ->willReturn('nl');

        $controller = new LocaleController($this->app[Component::CORE], $repositoryMock);

        /** @var RedirectResponse $return */
        $return = $controller->setLocale($requestMock);

        static::assertInstanceOf(RedirectResponse::class, $return);

        static::assertEquals('nl', session()->get('cms::locale'));
    }

    /**
     * @test
     */
    function it_redirects_if_locale_to_set_is_unavailable()
    {
        /** @var LocaleRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject $repositoryMock */
        /** @var SetLocaleRequest|\PHPUnit_Framework_MockObject_MockObject $requestMock */
        $repositoryMock = $this->getMockBuilder(LocaleRepositoryInterface::class)->getMock();
        $requestMock    = $this->getMockBuilder(SetLocaleRequest::class)->getMock();

        $repositoryMock->expects(static::atLeastOnce())
            ->method('isAvailable')
            ->with('nl')
            ->willReturn(false);

        $requestMock->expects(static::atLeastOnce())
            ->method('input')
            ->with('locale')
            ->willReturn('nl');

        $controller = new LocaleController($this->app[Component::CORE], $repositoryMock);

        /** @var RedirectResponse $return */
        $return = $controller->setLocale($requestMock);

        static::assertInstanceOf(RedirectResponse::class, $return);

        static::assertFalse(session()->has('cms::locale'));
    }

}
