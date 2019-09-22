<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Test\Modules;

use Czim\CmsCore\Contracts\Auth\AclRepositoryInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Modules\ModuleInterface;
use Czim\CmsCore\Modules\ModuleManager;
use Czim\CmsCore\Test\Helpers\Modules\SimpleAssociatedTestModule;
use Czim\CmsCore\Test\Helpers\Modules\SimpleTestModule;
use Czim\CmsCore\Test\Helpers\Modules\SimpleTestModuleGenerator;
use Czim\CmsCore\Test\Helpers\Modules\SimpleTestModuleWithServiceProviders;
use Czim\CmsCore\Test\Helpers\Modules\TestModuleWithRoutes;
use Czim\CmsCore\Test\CmsBootTestCase;
use Illuminate\Routing\Router;
use Illuminate\Support\Collection;

class ModuleManagerTest extends CmsBootTestCase
{

    /**
     * @test
     */
    function it_returns_its_version_number()
    {
        $manager = $this->makeManager();

        static::assertRegExp('#^\d+\.\d+\.\d+$#', $manager->version());
    }

    /**
     * @test
     */
    function it_returns_whether_it_was_initialized()
    {
        $manager = $this->makeManager();

        static::assertFalse($manager->isInitialized(), 'Should not report initialized directly after construct');

        $manager->initialize();

        static::assertTrue($manager->isInitialized(), 'Should report initialized after initialize()');
    }

    /**
     * @test
     */
    function it_loads_configured_modules_on_initialization()
    {
        $manager = $this->makeManager();

        $manager->initialize();

        $modules = $manager->getModules();

        static::assertInstanceOf(Collection::class, $modules, 'getModules() did not return Collection');
        static::assertCount(2, $modules, "Two modules should be loaded by default");
        static::assertTrue($modules->has('test-module'), 'Test Module should be loaded');
        static::assertTrue($modules->has('associated-test-module'), 'Associated Test Module should be loaded');
    }

    /**
     * @test
     */
    function it_initializes_with_injected_module_array_without_loading_configured_modules()
    {
        $manager = $this->makeManager();

        $manager->initialize([ SimpleAssociatedTestModule::class ]);

        $modules = $manager->getModules();

        static::assertCount(1, $modules, 'There should be only one module loaded');
        static::assertEquals('associated-test-module', $modules->first()->getKey(), 'The wrong module was loaded');
    }

    /**
     * @test
     */
    function it_loads_modules_generated_by_a_module_generator()
    {
        $manager = $this->makeManager();

        $manager->initialize([
            SimpleTestModuleGenerator::class,
        ]);

        static::assertInstanceOf(ModuleInterface::class, $manager->get('associated-test-module'));
    }

    /**
     * @test
     */
    function it_returns_loaded_modules_by_key()
    {
        $manager = $this->makeManager();

        $manager->initialize();

        static::assertInstanceOf(ModuleInterface::class, $manager->get('test-module'));
        static::assertInstanceOf(ModuleInterface::class, $manager->get('associated-test-module'));
    }

    /**
     * @test
     */
    function it_returns_loaded_modules_by_associated_class()
    {
        $manager = $this->makeManager();

        $manager->initialize();

        static::assertInstanceOf(ModuleInterface::class, $manager->getByAssociatedClass(ModuleManager::class));
    }

    /**
     * @test
     */
    function it_returns_false_when_getting_unloaded_module_by_key()
    {
        $manager = $this->makeManager();

        $manager->initialize();

        static::assertSame(false, $manager->get('does-not-exist'));
    }

    /**
     * @test
     */
    function it_returns_false_when_getting_unloaded_module_by_associated_class()
    {
        $manager = $this->makeManager();

        $manager->initialize();

        static::assertSame(false, $manager->getByAssociatedClass('Some\\Unassociated\\Class'));
    }

    /**
     * @test
     */
    function it_returns_whether_a_module_is_loaded_by_key()
    {
        $manager = $this->makeManager();

        $manager->initialize();

        static::assertTrue($manager->has('test-module'), 'Should return true for loaded module');
        static::assertFalse($manager->has('not-loaded'), 'Should return false for unloaded module');
    }

    /**
     * @test
     */
    function it_throws_an_exception_if_the_same_module_key_is_encountered_more_than_once()
    {
        $this->expectException(\Exception::class);

        $manager = $this->makeManager();

        $manager->initialize([
            SimpleTestModule::class,
            SimpleTestModule::class,
        ]);
    }

    /**
     * @test
     */
    function it_builds_web_routes_for_loaded_modules_on_a_given_router()
    {
        $manager = $this->makeManager();

        $manager->initialize([ TestModuleWithRoutes::class ]);

        $router = $this->getMockRouter();
        $router->expects(static::once())->method('get')->with('test', 'SomeController@index');
        $router->expects(static::once())->method('post')->with('test', 'SomeController@store');

        $manager->mapWebRoutes($router);
    }

    /**
     * @test
     */
    function it_builds_api_routes_for_loaded_modules_on_a_given_router()
    {
        $manager = $this->makeManager();

        $manager->initialize([ TestModuleWithRoutes::class ]);

        $router = $this->getMockRouter();
        $router->expects(static::once())->method('get')->with('test', 'SomeApiController@index');
        $router->expects(static::once())->method('post')->with('test', 'SomeApiController@store');

        $manager->mapApiRoutes($router);
    }

    /**
     * @test
     */
    function it_sorts_the_modules_by_name()
    {
        $manager = $this->makeManager();

        $manager->initialize([
            SimpleTestModuleWithServiceProviders::class,
            SimpleTestModule::class,
        ]);

        $modules = $manager->getModules();

        static::assertCount(2, $modules);
        static::assertEquals('test-module', $modules->first()->getKey());
        static::assertEquals('test-module-with-service-providers', $modules->last()->getKey());
    }

    /**
     * @test
     */
    function it_returns_permissions_for_all_modules_from_the_acl_repository()
    {
        $mockAcl = $this->getMockAcl();

        $mockAcl->expects(static::once())
                ->method('getAllPermissions')
                ->willReturn(['test']);

        $manager = new ModuleManager($this->getMockCore(), $mockAcl);

        static::assertEquals(['test'], $manager->getAllPermissions());
    }

    /**
     * @test
     */
    function it_returns_permissions_for_a_module_from_the_acl_repository()
    {
        $mockAcl = $this->getMockAcl();

        $mockAcl->expects(static::once())
            ->method('getModulePermissions')
            ->with('testing')
            ->willReturn(['test']);

        $manager = new ModuleManager($this->getMockCore(), $mockAcl);

        static::assertEquals(['test'], $manager->getModulePermissions('testing'));
    }


    /**
     * @test
     */
    function it_throws_an_exception_when_trying_to_load_a_nonexistant_class()
    {
        $this->expectException(\InvalidArgumentException::class);

        $manager = $this->makeManager();

        $manager->initialize([
            'Does\\NotExist',
        ]);
    }

    /**
     * @test
     */
    function it_throws_an_exception_when_trying_to_load_a_noninstantiable_class()
    {
        $this->expectException(\InvalidArgumentException::class);

        $manager = $this->makeManager();

        $manager->initialize([
            \Czim\CmsCore\Test\Helpers\NonInstantiableInterface::class,
        ]);
    }

    /**
     * @test
     */
    function it_throws_an_exception_when_trying_to_load_a_class_with_uninstantiable_dependencies()
    {
        $this->expectException(\InvalidArgumentException::class);

        $manager = $this->makeManager();

        $manager->initialize([
            \Czim\CmsCore\Test\Helpers\NonInstantiableClassWithDependency::class,
        ]);
    }

    /**
     * @test
     */
    function it_throws_an_exception_when_trying_to_load_an_invalid_module_class()
    {
        $this->expectException(\InvalidArgumentException::class);

        $manager = $this->makeManager();

        $manager->initialize([
            \Czim\CmsCore\Test\Helpers\Core\MockApiBootChecker::class,
        ]);
    }


    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|CoreInterface
     */
    protected function getMockCore()
    {
        $mock = $this->getMockBuilder(CoreInterface::class)->getMock();

        $mock->method('moduleConfig')
             ->with('modules', static::anything())
             ->willReturn([
                 SimpleTestModule::class,
                 SimpleAssociatedTestModule::class,
             ]);

        return $mock;
    }

    /**
     * @return ModuleManager
     */
    protected function makeManager()
    {
        return new ModuleManager($this->getMockCore(), $this->getMockAcl());
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

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AclRepositoryInterface
     */
    protected function getMockAcl()
    {
        return $this->getMockBuilder(AclRepositoryInterface::class)
            ->getMock();
    }
}
