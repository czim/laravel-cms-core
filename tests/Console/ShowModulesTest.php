<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Test\Console;

use Czim\CmsCore\Contracts\Modules\ModuleInterface;
use Czim\CmsCore\Contracts\Modules\ModuleManagerInterface;
use Czim\CmsCore\Test\CmsBootTestCase;
use Illuminate\Support\Collection;
use Mockery;
use Mockery\Mock;
use Mockery\MockInterface;

class ShowModulesTest extends CmsBootTestCase
{

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
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

        /** @var ModuleManagerInterface|Mock|MockInterface $modulesMock */
        $modulesMock = Mockery::mock(ModuleManagerInterface::class);

        $modulesMock->shouldReceive('getModules')->once()->andReturn($modules);

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

        /** @var ModuleManagerInterface|Mock|MockInterface $modulesMock */
        $modulesMock = Mockery::mock(ModuleManagerInterface::class);

        $modulesMock->shouldReceive('getModules')->once()->andReturn($modules);

        $this->app->instance(ModuleManagerInterface::class, $modulesMock);

        static::assertEquals(0, $this->artisan('cms:modules:show'));

        $output = $this->getArtisanOutput();

        static::assertRegexp('#test-a\s+name\s*:\s*Test Module\s+version\s*:\s*1\.0\.0#i', $output);
        static::assertRegexp(
            '#test-b\s+name\s*:\s*Test B\s+version\s*:\s*1\.0\.0\s+'
            . 'associated class\s*:\s*' . preg_quote(static::class, '#') . '#i',
            $output
        );
    }

    /**
     * @test
     */
    function it_shows_information_about_a_specific_module_by_key()
    {
        /** @var ModuleManagerInterface|Mock|MockInterface $modulesMock */
        $modulesMock = Mockery::mock(ModuleManagerInterface::class);

        $modulesMock->shouldReceive('getModules')->never();
        $modulesMock->shouldReceive('get')->once()->with('test-b')
            ->andReturn($this->getMockModule('test-b', 'Test B', static::class));

        $this->app->instance(ModuleManagerInterface::class, $modulesMock);

        static::assertEquals(0, $this->artisan('cms:modules:show', [ 'module' => 'test-b' ]));

        $output = $this->getArtisanOutput();

        static::assertNotRegexp('#test-a#i', $output);
        static::assertNotRegexp('#test-c#i', $output);
        static::assertRegexp(
            '#test-b\s+name\s*:\s*Test B\s+version\s*:\s*1\.0\.0\s+'
            . 'associated class\s*:\s*' . preg_quote(static::class, '#') . '#i',
            $output
        );
    }

    /**
     * @test
     */
    function it_reports_a_warning_if_a_module_could_not_be_found_by_key()
    {
        /** @var ModuleManagerInterface|Mock|MockInterface $modulesMock */
        $modulesMock = Mockery::mock(ModuleManagerInterface::class);

        $modulesMock->shouldReceive('getModules')->andReturn(new Collection());

        static::assertEquals(0, $this->artisan('cms:modules:show', [ 'module' => 'test-b' ]));

        $output = $this->getArtisanOutput();

        static::assertRegexp('#\s*unable to find .* [\'"]?test-b[\'"]?#i', $output);
    }


    /**
     * @param string      $key
     * @param string      $name
     * @param null|string $class
     * @return ModuleInterface|Mock|MockInterface
     */
    protected function getMockModule(string $key = 'test', string $name = 'Test Module', ?string $class = null)
    {
        /** @var ModuleInterface|Mock|MockInterface $modulesMock */
        $mock = Mockery::mock(ModuleInterface::class);

        $mock->shouldReceive('getKey')->andReturn($key);
        $mock->shouldReceive('getName')->andReturn($name);
        $mock->shouldReceive('getVersion')->andReturn('1.0.0');
        $mock->shouldReceive('getAssociatedClass')->andReturn($class);

        return $mock;
    }

}
