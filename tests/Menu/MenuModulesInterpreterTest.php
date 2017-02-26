<?php
namespace Czim\CmsCore\Test\Menu;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Modules\Data\MenuPresenceInterface;
use Czim\CmsCore\Contracts\Modules\ModuleManagerInterface;
use Czim\CmsCore\Contracts\Modules\ModuleInterface;
use Czim\CmsCore\Contracts\Support\Data\MenuConfiguredModulesDataInterface;
use Czim\CmsCore\Menu\MenuModulesInterpreter;
use Czim\CmsCore\Support\Data\MenuPresence;
use Czim\CmsCore\Support\Enums\MenuPresenceType;
use Czim\CmsCore\Test\TestCase;
use Illuminate\Support\Collection;

class MenuModulesInterpreterTest extends TestCase
{

    /**
     * Testing menu modules config.
     *
     * @var array
     */
    protected $menuModulesConfig = [];

    /**
     * @var Collection
     */
    protected $modules;


    /**
     * @test
     */
    function it_returns_standard_presences_if_no_menu_modules_are_configured()
    {
        $this->modules = $this->getMockModules();

        $interpreter = $this->makeModulesInterpreter();

        $data = $interpreter->interpret();

        static::assertInstanceof(MenuConfiguredModulesDataInterface::class, $data);
        static::assertEquals(['test-a', 'test-b'], $data->standard()->keys()->toArray(), 'Wrong module count, keys or order');
        static::assertInstanceOf(MenuPresenceInterface::class, head($data->standard()->get('test-a')));
        static::assertInstanceOf(MenuPresenceInterface::class, head($data->standard()->get('test-b')));
    }

    /**
     * @test
     */
    function it_returns_standard_presences_in_configured_ordering()
    {
        $this->modules = $this->getMockModules();

        $this->menuModulesConfig = [
            'test-b',
            'test-a',
        ];

        $interpreter = $this->makeModulesInterpreter();

        $data = $interpreter->interpret();

        static::assertInstanceof(MenuConfiguredModulesDataInterface::class, $data);
        static::assertEquals(['test-b', 'test-a'], $data->standard()->keys()->toArray(), 'Wrong module order (or keys array)');
        static::assertInstanceOf(MenuPresenceInterface::class, head($data->standard()->get('test-a')));
        static::assertInstanceOf(MenuPresenceInterface::class, head($data->standard()->get('test-b')));
    }

    /**
     * @test
     */
    function it_allows_overriding_of_module_presence_data_by_setting_values_in_module_configuration()
    {
        $this->modules = collect([
            'test-a' => $this->getMockModuleWithPresenceInstance(false),
        ]);

        $this->menuModulesConfig = [
            'test-a' => [
                // overrule presence to disable it
                'presence' => [
                    'id'     => 'test-overruled',
                    'type'   => MenuPresenceType::ACTION,
                    'label'  => 'something',
                    'action' => 'test',
                ],
            ],
        ];

        $interpreter = $this->makeModulesInterpreter();

        $data = $interpreter->interpret();

        static::assertInstanceOf(MenuPresenceInterface::class, head($data->standard()->first()));
        static::assertEquals('test-overruled', head($data->standard()->first())->id(), 'Presence should be overruled');
    }


    // ------------------------------------------------------------------------------
    //      Helpers
    // ------------------------------------------------------------------------------

    /**
     * @return CoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockCore()
    {
        $mock = $this->getMockBuilder(CoreInterface::class)->getMock();

        $mock->method('modules')
            ->willReturn($this->getMockModuleManager($this->modules));

        $mock->method('moduleConfig')
            ->willReturnCallback(function ($key) {
                switch ($key) {
                    case 'menu.modules':
                        return $this->menuModulesConfig;
                }

                return null;
            });

        return $mock;
    }

    /**
     * @param null|Collection $modules
     * @return ModuleManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockModuleManager($modules = null)
    {
        $mock = $this->getMockBuilder(ModuleManagerInterface::class)->getMock();

        $modules = $modules ?: new Collection;

        $mock->expects(static::once())
            ->method('getModules')
            ->willReturn($modules);

        return $mock;
    }

    /**
     * @return Collection
     */
    protected function getMockModules()
    {
        return new Collection([
            'test-a' => $this->getMockModuleWithPresenceInstance(),
            'test-b' => $this->getMockModuleWithPermissions(),
        ]);
    }

    /**
     * @param bool $expected    whether the menu presence call is expected
     * @return \PHPUnit_Framework_MockObject_MockObject|ModuleInterface
     */
    protected function getMockModuleWithPresenceInstance($expected = true)
    {
        $mock = $this->getMockBuilder(ModuleInterface::class)->getMock();

        $mock->method('getKey')->willReturn('test-a');

        $mock->expects($expected ? static::once() : static::any())
            ->method('getMenuPresence')
            ->willReturn(
                new MenuPresence([
                    'id'    => 'test-a',
                    'type'  => MenuPresenceType::ACTION,
                    'label' => 'something',
                ])
            );

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ModuleInterface
     */
    protected function getMockModuleWithPermissions()
    {
        $mock = $this->getMockBuilder(ModuleInterface::class)->getMock();

        $mock->method('getKey')->willReturn('test-b');

        $mock->expects(static::once())
            ->method('getMenuPresence')
            ->willReturn([
                'id'          => 'test-b',
                'type'        => MenuPresenceType::ACTION,
                'label'       => 'something',
                'action'      => 'test',
                'permissions' => ['can.do.something', 'can.do.more'],
            ]);

        return $mock;
    }

    /**
     * @return MenuModulesInterpreter
     */
    protected function makeModulesInterpreter()
    {
        return new MenuModulesInterpreter($this->getMockCore());
    }

}

