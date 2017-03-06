<?php
namespace Czim\CmsCore\Console;

use Czim\CmsCore\Contracts\Menu\MenuRepositoryInterface;
use Czim\CmsCore\Support\Data\MenuPresence;
use Czim\CmsCore\Support\Enums\MenuPresenceType;
use Czim\CmsCore\Test\CmsBootTestCase;
use Illuminate\Support\Collection;

class ShowMenuTest extends CmsBootTestCase
{

    /**
     * @test
     */
    function it_shows_the_menu_layout_using_the_menu_repository()
    {
        $layout = new Collection([
            new MenuPresence([
                'type'     => MenuPresenceType::GROUP,
                'label'    => 'Group Label',
                'children' => [
                    new MenuPresence([
                        'type'        => MenuPresenceType::ACTION,
                        'label'       => 'Action Label',
                        'action'      => 'cms::test.link',
                        'permissions' => 'test.permission',
                    ])
                ]
            ])
        ]);

        $repositoryMock = $this->getMockBuilder(MenuRepositoryInterface::class)->getMock();

        $repositoryMock->expects(static::once())
            ->method('ignorePermission')
            ->willReturnSelf();

        $repositoryMock->expects(static::once())
            ->method('initialize');

        $repositoryMock->expects(static::once())
            ->method('getMenuLayout')
            ->willReturn($layout);

        $this->app->instance(MenuRepositoryInterface::class, $repositoryMock);

        static::assertEquals(0, $this->artisan('cms:menu:show'));

        $output = $this->getArtisanOutput();

        static::assertRegexp('#\sgroup\s*label\s*:\s*group label\s#is', $output);
        static::assertRegexp('#\saction\s*label\s*:\s*action label\s*action\s*:\s*cms::test\.link#is', $output);
    }

    /**
     * @test
     */
    function it_shows_the_layout_and_whether_translations_exist_for_labels_for_a_given_locale()
    {
        $layout = new Collection([
            new MenuPresence([
                'type'             => MenuPresenceType::ACTION,
                'label_translated' => 'test.translation',
                'action'           => 'cms::test.link',
            ])
        ]);

        $repositoryMock = $this->getMockBuilder(MenuRepositoryInterface::class)->getMock();
        $repositoryMock->method('ignorePermission')->willReturnSelf();
        $repositoryMock->method('initialize');
        $repositoryMock->method('getMenuLayout')->willReturn($layout);

        $this->app->instance(MenuRepositoryInterface::class, $repositoryMock);


        // Translation key for EN should show up as unset
        static::assertEquals(0, $this->artisan('cms:menu:show', ['--locale' => 'en']));
        $output = $this->getArtisanOutput();

        static::assertRegexp('#\s*test\.translation\s*\(en unset\)#is', $output);

        // Set translation key for NL
        // This doesn't work anymore in Laravel 5.4
        // todo: figure out why and fix it
        //$this->app->setLocale('nl');
        //$this->app['translator']->addLines(['test.translation' => 'exists'], 'nl', '*');
        //
        //static::assertEquals(0, $this->artisan('cms:menu:show', ['--locale' => 'nl']));
        //$output = $this->getArtisanOutput();
        //
        //static::assertNotRegexp('#unset\)#', $output);
    }

}
