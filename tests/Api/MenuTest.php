<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Test\Api;

use Czim\CmsCore\Support\Enums\MenuPresenceMode;
use Czim\CmsCore\Support\Enums\MenuPresenceType;
use Czim\CmsCore\Test\ApiTestCase;
use Czim\CmsCore\Test\Helpers\Modules\SimpleAssociatedTestModule;
use Czim\CmsCore\Test\Helpers\Modules\SimpleTestModule;
use Illuminate\Foundation\Application;

class MenuTest extends ApiTestCase
{

    /**
     * {@inheritdoc}
     */
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $this->setUpBasicMenuLayout($app);
    }

    /**
     * @test
     */
    function it_responds_with_a_nested_menu_layout()
    {
        $response = $this->call('get', 'cms-api/meta/menu');

        $response->assertStatus(200);

        $response = $response->decodeResponseJson();


        static::assertArrayHasKey('layout', $response);

        static::assertCount(1, $response['layout']);
        static::assertEquals('group-a', $response['layout'][0]['id']);
        static::assertEquals('group', $response['layout'][0]['type']);
        static::assertEquals('display name', $response['layout'][0]['label']);
        static::assertEquals('home', $response['layout'][0]['icon']);
        static::assertNull($response['layout'][0]['action']);
        static::assertEquals([], $response['layout'][0]['parameters']);
        static::assertEquals([], $response['layout'][0]['permissions']);

        static::assertCount(2, $response['layout'][0]['children']);
        static::assertEquals('test-1', $response['layout'][0]['children'][0]['id']);
        static::assertEquals('action', $response['layout'][0]['children'][0]['type']);
        static::assertEquals('Testing A', $response['layout'][0]['children'][0]['label']);
        static::assertNull($response['layout'][0]['children'][0]['icon']);
        static::assertEquals('cms::test.route.a', $response['layout'][0]['children'][0]['action']);

        static::assertEquals('group-b', $response['layout'][0]['children'][1]['id']);
        static::assertEquals('group', $response['layout'][0]['children'][1]['type']);
        static::assertEquals('display name nested', $response['layout'][0]['children'][1]['label']);
        static::assertNull($response['layout'][0]['children'][1]['icon']);
        static::assertNull($response['layout'][0]['children'][1]['action']);

        static::assertCount(1, $response['layout'][0]['children'][1]['children']);
        static::assertEquals('test-2', $response['layout'][0]['children'][1]['children'][0]['id']);
        static::assertEquals('link', $response['layout'][0]['children'][1]['children'][0]['type']);
        static::assertEquals('Testing B', $response['layout'][0]['children'][1]['children'][0]['label']);
        static::assertEquals('shopping-cart', $response['layout'][0]['children'][1]['children'][0]['icon']);
        static::assertEquals('https://www.google.com', $response['layout'][0]['children'][1]['children'][0]['action']);
    }

    /**
     * @param Application $app
     */
    protected function setUpBasicMenuLayout(Application $app): void
    {
        $app['config']->set('cms-modules.modules', [
            SimpleTestModule::class,
            SimpleAssociatedTestModule::class,
        ]);

        $app['config']->set('cms-modules.menu.layout', [
            'group-a' => [
                'label'            => 'display name',
                'label_translated' => 'translated.group.key',
                'icon'             => 'home',
                'children' => [
                    'test-module',
                    'group-b' => [
                        'label' => 'display name nested',
                        'children' => [
                            'associated-test-module',
                        ],
                    ],
                ],
            ],
        ]);

        $app['config']->set('cms-modules.menu.modules', [
            'test-module' => [
                'mode'   => MenuPresenceMode::ADD,
                'type'   => MenuPresenceType::ACTION,
                'id'     => 'test-1',
                'label'  => 'Testing A',
                'action' => 'cms::test.route.a',
            ],
            'associated-test-module' => [
                'mode'   => MenuPresenceMode::ADD,
                'type'   => MenuPresenceType::LINK,
                'id'     => 'test-2',
                'icon'   => 'shopping-cart',
                'label'  => 'Testing B',
                'action' => 'https://www.google.com',
            ],
        ]);
    }

}
