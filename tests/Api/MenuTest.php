<?php
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
    protected function getEnvironmentSetUp($app)
    {
        $this->setUpBasicMenuLayout($app);

        parent::getEnvironmentSetUp($app);
    }

    /**
     * @test
     */
    function it_responds_with_a_nested_menu_layout()
    {
        $this->call('get', 'cms-api/meta/menu');

        $this->seeStatusCode(200)
             ->seeJson();

        $response = $this->decodeResponseJson();


        static::assertArrayHasKey('layout', $response);
        static::assertCount(1, $response['layout']);
        static::assertArraySubset([
            'id'          => 'group-a',
            'type'        => 'group',
            'label'       => 'display name',
            'icon'        => 'home',
            'action'      => null,
            'parameters'  => [],
            'permissions' => [],
        ], $response['layout'][0]);

        static::assertCount(2, $response['layout'][0]['children']);
        static::assertArraySubset([
            'id'          => 'test-1',
            'type'        => 'action',
            'label'       => 'Testing A',
            'icon'        => null,
            'action'      => 'cms::test.route.a',
        ], $response['layout'][0]['children'][0]);

        static::assertArraySubset([
            'id'          => 'group-b',
            'type'        => 'group',
            'label'       => 'display name nested',
            'icon'        => null,
            'action'      => null,
        ], $response['layout'][0]['children'][1]);

        static::assertCount(1, $response['layout'][0]['children'][1]['children']);
        static::assertArraySubset([
            'id'     => 'test-2',
            'type'   => 'link',
            'icon'   => 'shopping-cart',
            'label'  => 'Testing B',
            'action' => 'https://www.google.com',
        ], $response['layout'][0]['children'][1]['children'][0]);
    }

    /**
     * @param Application $app
     */
    protected function setUpBasicMenuLayout(Application $app)
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
