<?php
namespace Czim\CmsCore\Console;

use Czim\CmsCore\Contracts\Modules\ModuleInterface;
use Czim\CmsCore\Contracts\Modules\ModuleManagerInterface;
use Czim\CmsCore\Test\CmsBootTestCase;
use Illuminate\Support\Collection;

class ShowModulesTest extends CmsBootTestCase
{

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        $this->withoutMockingConsoleOutput();
    }

    /**
     * @test
     */
    function it_shows_the_keys_of_loaded_modules()
    {
        $modules = new Collection([
            'test-a' => $this->getMockModule('test-a'),
            'test-b' => $this->getMockModule('test-b'),
            'test-c' => $this->getMockModule('test-c'),
        ]);

        $modulesMock = $this->getMockBuilder(ModuleManagerInterface::class)->getMock();
        $modulesMock->expects(static::once())
            ->method('getModules')
            ->willReturn($modules);

        $this->app->instance(ModuleManagerInterface::class, $modulesMock);

        static::assertEquals(0, $this->artisan('cms:modules:show', ['--keys' => true]));

        $output = $this->getArtisanOutput();

        static::assertRegexp('#test-a\s#i', $output);
        static::assertRegexp('#test-b\s#i', $output);
        static::assertRegexp('#test-c\s#i', $output);
    }

    /**
     * @test
     */
    function it_shows_information_about_loaded_modules()
    {
        $modules = new Collection([
            'test-a' => $this->getMockModule('test-a'),
            'test-b' => $this->getMockModule('test-b', 'Test B', static::class),
        ]);

        $modulesMock = $this->getMockBuilder(ModuleManagerInterface::class)->getMock();
        $modulesMock->expects(static::once())
            ->method('getModules')
            ->willReturn($modules);

        $this->app->instance(ModuleManagerInterface::class, $modulesMock);

        static::assertEquals(0, $this->artisan('cms:modules:show'));

        $output = $this->getArtisanOutput();

        static::assertRegexp('#test-a\s+name\s*:\s*Test Module\s+version\s*:\s*1\.0\.0#i', $output);
        static::assertRegexp(
            '#test-b\s+name\s*:\s*Test B\s+version\s*:\s*1\.0\.0\s+'
            . 'associated class\s*:\s*' . preg_quote(static::class) . '#i',
            $output
        );
    }

    /**
     * @test
     */
    function it_shows_information_about_a_specific_module_by_key()
    {
        $modulesMock = $this->getMockBuilder(ModuleManagerInterface::class)->getMock();
        $modulesMock->expects(static::never())->method('getModules');
        $modulesMock->expects(static::once())
            ->method('get')
            ->with('test-b')
            ->willReturn($this->getMockModule('test-b', 'Test B', static::class));

        $this->app->instance(ModuleManagerInterface::class, $modulesMock);

        static::assertEquals(0, $this->artisan('cms:modules:show', [ 'module' => 'test-b' ]));

        $output = $this->getArtisanOutput();

        static::assertNotRegexp('#test-a#i', $output);
        static::assertNotRegexp('#test-c#i', $output);
        static::assertRegexp(
            '#test-b\s+name\s*:\s*Test B\s+version\s*:\s*1\.0\.0\s+'
            . 'associated class\s*:\s*' . preg_quote(static::class) . '#i',
            $output
        );
    }

    /**
     * @test
     */
    function it_reports_a_warning_if_a_module_could_not_be_found_by_key()
    {
        $modulesMock = $this->getMockBuilder(ModuleManagerInterface::class)->getMock();
        $modulesMock->method('getModules')->willReturn(new Collection);

        static::assertEquals(0, $this->artisan('cms:modules:show', [ 'module' => 'test-b' ]));

        $output = $this->getArtisanOutput();

        static::assertRegexp('#\s*unable to find .* [\'"]?test-b[\'"]?#i', $output);
    }


    /**
     * @param string      $key
     * @param string      $name
     * @param null|string $class
     * @return ModuleInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockModule($key = 'test', $name = 'Test Module', $class = null)
    {
        $mock = $this->getMockBuilder(ModuleInterface::class)->getMock();

        $mock->method('getKey')->willReturn($key);
        $mock->method('getName')->willReturn($name);
        $mock->method('getVersion')->willReturn('1.0.0');
        $mock->method('getAssociatedClass')->willReturn($class);

        return $mock;
    }

}
