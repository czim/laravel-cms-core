<?php
namespace Czim\CmsCore\Test\Modules;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Modules\ModuleInterface;
use Czim\CmsCore\Modules\Manager\Manager;
use Czim\CmsCore\Test\Helpers\Modules\SimpleAssociatedTestModule;
use Czim\CmsCore\Test\Helpers\Modules\SimpleTestModule;
use Czim\CmsCore\Test\Helpers\Modules\SimpleTestModuleWithSameServiceProvider;
use Czim\CmsCore\Test\Helpers\Modules\SimpleTestModuleWithServiceProviders;
use Czim\CmsCore\Test\Helpers\Modules\TestModuleWithRoutes;
use Czim\CmsCore\Test\TestCase;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;

class ManagerTest extends TestCase
{

    /**
     * @test
     */
    function it_initializes_succesfully()
    {
        $manager = new Manager($this->getMockCore());

        $manager->initialize();
    }

    /**
     * @test
     */
    function it_loads_configured_modules_on_initialization()
    {
        $manager = new Manager($this->getMockCore());

        $manager->initialize();

        $modules = $manager->getModules();

        $this->assertInstanceOf(Collection::class, $modules, 'getModules() did not return Collection');
        $this->assertCount(2, $modules, "Two modules should be loaded by default");
        $this->assertTrue($modules->has('test-module'), 'Test Module should be loaded');
        $this->assertTrue($modules->has('associated-test-module'), 'Associated Test Module should be loaded');
    }

    /**
     * @test
     */
    function it_initializes_with_injected_module_array_without_loading_configured_modules()
    {
        $manager = new Manager($this->getMockCore());

        $manager->initialize([ SimpleAssociatedTestModule::class ]);

        $modules = $manager->getModules();

        $this->assertCount(1, $modules, 'There should be only one module loaded');
        $this->assertEquals('associated-test-module', $modules->first()->getKey(), 'The wrong module was loaded');
    }

    /**
     * @test
     */
    function it_returns_loaded_modules_by_key()
    {
        $manager = new Manager($this->getMockCore());

        $manager->initialize();

        $this->assertInstanceOf(ModuleInterface::class, $manager->get('associated-test-module'));
    }

    /**
     * @test
     */
    function it_returns_loaded_modules_by_associated_class()
    {
        $manager = new Manager($this->getMockCore());

        $manager->initialize();

        $this->assertInstanceOf(ModuleInterface::class, $manager->getByAssociatedClass(Manager::class));
    }

    /**
     * @test
     */
    function it_returns_false_when_getting_unloaded_module_by_key()
    {
        $manager = new Manager($this->getMockCore());

        $manager->initialize();

        $this->assertSame(false, $manager->get('does-not-exist'));
    }

    /**
     * @test
     */
    function it_returns_false_when_getting_unloaded_module_by_associated_class()
    {
        $manager = new Manager($this->getMockCore());

        $manager->initialize();

        $this->assertSame(false, $manager->getByAssociatedClass('Some\\Unassociated\\Class'));
    }

    /**
     * @test
     */
    function it_returns_whether_a_module_is_loaded_by_key()
    {
        $manager = new Manager($this->getMockCore());

        $manager->initialize();

        $this->assertTrue($manager->has('test-module'), 'Should return true for loaded module');
        $this->assertFalse($manager->has('not-loaded'), 'Should return false for unloaded module');
    }

    /**
     * @test
     * @expectedException \Exception
     */
    function it_throws_an_exception_if_the_same_module_key_is_encountered_more_than_once()
    {
        $manager = new Manager($this->getMockCore());

        $manager->initialize([
            SimpleTestModule::class,
            SimpleTestModule::class,
        ]);
    }
    
    /**
     * @test
     */
    function it_returns_service_providers_defined_by_modules()
    {
        $manager = new Manager($this->getMockCore());

        $manager->initialize([ SimpleTestModuleWithServiceProviders::class ]);

        $exampleModule = new SimpleTestModuleWithServiceProviders();

        $this->assertEquals(
            $exampleModule->getServiceProviders(),
            $manager->getServiceProviders(),
            'Manager should return the same service providers as the module'
        );
    }

    /**
     * @test
     */
    function it_does_not_return_the_same_service_provider_more_than_once()
    {
        $manager = new Manager($this->getMockCore());

        $manager->initialize([
            SimpleTestModuleWithServiceProviders::class,
            SimpleTestModuleWithSameServiceProvider::class,
        ]);

        $exampleModule = new SimpleTestModuleWithServiceProviders();

        $this->assertEquals(
            $exampleModule->getServiceProviders(),
            $manager->getServiceProviders(),
            'Manager should not return more than two service providers'
        );
    }

    /**
     * @test
     */
    function it_builds_web_routes_for_loaded_modules_on_a_given_router()
    {
        $manager = new Manager($this->getMockCore());

        $manager->initialize([ TestModuleWithRoutes::class ]);

        $router = $this->getMockRouter();
        $router->expects($this->once())->method('get')->with('test', 'SomeController@index');
        $router->expects($this->once())->method('post')->with('test', 'SomeController@store');

        $manager->buildWebRoutes($router);
    }

    /**
     * @test
     */
    function it_builds_api_routes_for_loaded_modules_on_a_given_router()
    {
        $manager = new Manager($this->getMockCore());

        $manager->initialize([ TestModuleWithRoutes::class ]);

        $router = $this->getMockRouter();
        $router->expects($this->once())->method('get')->with('test', 'SomeApiController@index');
        $router->expects($this->once())->method('post')->with('test', 'SomeApiController@store');

        $manager->buildApiRoutes($router);
    }

    /**
     * @test
     */
    function it_sorts_the_service_providers()
    {
        // todo.. how should it actually sort them?
        $this->markTestIncomplete();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|CoreInterface
     */
    protected function getMockCore()
    {
        $mock = $this->getMockBuilder(CoreInterface::class)->getMock();

        $mock->method('moduleConfig')
             ->with('modules', $this->anything())
             ->willReturn([
                 SimpleTestModule::class,
                 SimpleAssociatedTestModule::class,
             ]);

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Router
     */
    protected function getMockRouter()
    {
        return $this->getMockBuilder(Router::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

}
