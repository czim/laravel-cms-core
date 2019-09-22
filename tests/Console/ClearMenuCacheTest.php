<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Console;

use Czim\CmsCore\Contracts\Menu\MenuRepositoryInterface;
use Czim\CmsCore\Test\CmsBootTestCase;

class ClearMenuCacheTest extends CmsBootTestCase
{

    /**
     * @test
     */
    function it_clears_cache_using_the_menu_repository()
    {
        $repositoryMock = $this->getMockBuilder(MenuRepositoryInterface::class)->getMock();
        $repositoryMock->expects(static::once())
            ->method('clearCache');

        $this->app->instance(MenuRepositoryInterface::class, $repositoryMock);

        $this->artisan('cms:menu:clear')->assertExitCode(0);
    }

}
