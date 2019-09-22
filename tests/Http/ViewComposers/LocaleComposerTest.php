<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Test\Http\Middleware;

use Czim\CmsCore\Contracts\Support\Localization\LocaleRepositoryInterface;
use Czim\CmsCore\Http\ViewComposers\LocaleComposer;
use Czim\CmsCore\Test\TestCase;
use Illuminate\Contracts\View\View;

class LocaleComposerTest extends TestCase
{

    /**
     * @test
     */
    function it_composes_locale_data()
    {
        /** @var LocaleRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject $repositoryMock */
        /** @var View|\PHPUnit_Framework_MockObject_MockObject $viewMock */
        $repositoryMock = $this->getMockBuilder(LocaleRepositoryInterface::class)->getMock();
        $viewMock       = $this->getMockBuilder(View::class)->getMock();

        $viewMock->expects(static::once())
            ->method('with')
            ->with([
                'localized'        => true,
                'currentLocale'    => 'en',
                'availableLocales' => ['nl', 'en'],
            ]);

        $repositoryMock->expects(static::once())
            ->method('isLocalized')
            ->willReturn(true);

        $repositoryMock->expects(static::once())
            ->method('getAvailable')
            ->willReturn(['nl', 'en']);

        $composer = new LocaleComposer($repositoryMock);
        $composer->compose($viewMock);
    }

}
