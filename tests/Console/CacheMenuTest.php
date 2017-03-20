<?php
namespace Czim\CmsCore\Console;

use Czim\CmsCore\Contracts\Menu\MenuRepositoryInterface;
use Czim\CmsCore\Test\CmsBootTestCase;

class CacheMenuTest extends CmsBootTestCase
{

    /**
     * @test
     */
    function it_caches_using_the_menu_repository()
    {
        $repositoryMock = $this->getMockBuilder(MenuRepositoryInterface::class)->getMock();
        $repositoryMock->expects(static::once())
            ->method('writeCache');

        $this->app->instance(MenuRepositoryInterface::class, $repositoryMock);

        static::assertEquals(0, $this->artisan('cms:menu:cache'));
    }

}
