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
        $this->call('get', 'cms-api/meta/modules');

        $this->seeStatusCode(200)
             ->seeJson()
             ->seeJsonContains([
                 'key'  => 'associated-test-module',
                 'name' => 'Test Associated Module',
             ])
             ->seeJsonContains([
                'key'  => 'test-module',
                'name' => 'Test Module',
             ])
             ->seeJsonContains([
                'key'  => 'test-module-with-service-providers',
                'name' => 'Test Module With Service Providers',
             ]);

        $response = $this->decodeResponseJson();

        static::assertArrayHasKey('data', $response);
        static::assertCount(3, $response['data']);
    }
    
    /**
     * @test
     */
    function it_responds_with_information_on_a_single_module_by_key()
    {
        $this->call('get', 'cms-api/meta/modules/test-module-with-service-providers');

        $this->seeStatusCode(200)
            ->seeJson()
            ->seeJsonContains([
                'key'  => 'test-module-with-service-providers',
                'name' => 'Test Module With Service Providers',
            ]);

        $response = $this->decodeResponseJson();

        static::assertArrayHasKey('data', $response);
    }

    /**
     * @test
     */
    function it_responds_with_404_if_single_module_by_key_was_not_found()
    {
        $this->call('get', 'cms-api/meta/modules/does-not-exist');

        $this->seeStatusCode(404);
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
