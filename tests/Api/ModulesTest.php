<?php
namespace Czim\CmsCore\Test\Api;

use Czim\CmsCore\Test\ApiTestCase;
use Czim\CmsCore\Test\Helpers\Modules\SimpleAssociatedTestModule;
use Czim\CmsCore\Test\Helpers\Modules\SimpleTestModule;
use Czim\CmsCore\Test\Helpers\Modules\SimpleTestModuleWithServiceProviders;
use Illuminate\Foundation\Application;

class ModulesTest extends ApiTestCase
{

    /**
     * {@inheritdoc}
     */
    protected function getEnvironmentSetUp($app)
    {
        $this->setUpBasicModules($app);

        parent::getEnvironmentSetUp($app);
    }

    /**
     * @test
     */
    function it_responds_with_a_list_of_modules()
    {
        $response = $this->call('get', 'cms-api/meta/modules');

        $response->assertStatus(200)
             ->assertJsonFragment([
                 'key'  => 'associated-test-module',
                 'name' => 'Test Associated Module',
             ])
             ->assertJsonFragment([
                'key'  => 'test-module',
                'name' => 'Test Module',
             ])
             ->assertJsonFragment([
                'key'  => 'test-module-with-service-providers',
                'name' => 'Test Module With Service Providers',
             ]);

        $response = $response->decodeResponseJson();

        static::assertArrayHasKey('data', $response);
        static::assertCount(3, $response['data']);
    }
    
    /**
     * @test
     */
    function it_responds_with_information_on_a_single_module_by_key()
    {
        $response = $this->call('get', 'cms-api/meta/modules/test-module-with-service-providers');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'key'  => 'test-module-with-service-providers',
                'name' => 'Test Module With Service Providers',
            ]);

        $response = $response->decodeResponseJson();

        static::assertArrayHasKey('data', $response);
    }

    /**
     * @test
     */
    function it_responds_with_404_if_single_module_by_key_was_not_found()
    {
        $response = $this->call('get', 'cms-api/meta/modules/does-not-exist');

        $response->assertStatus(404);
    }

    /**
     * @param Application $app
     */
    protected function setUpBasicModules(Application $app)
    {
        $app['config']->set('cms-modules.modules', [
            SimpleTestModule::class,
            SimpleAssociatedTestModule::class,
            SimpleTestModuleWithServiceProviders::class,
        ]);
    }

}
