<?php
namespace Czim\CmsCore\Test\Menu;

use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Contracts\Core\CacheInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Menu\MenuConfigInterpreterInterface;
use Czim\CmsCore\Contracts\Menu\MenuPermissionsFilterInterface;
use Czim\CmsCore\Contracts\Modules\Data\MenuPresenceInterface;
use Czim\CmsCore\Contracts\Support\Data\MenuLayoutDataInterface;
use Czim\CmsCore\Contracts\Support\Data\MenuPermissionsIndexDataInterface;
use Czim\CmsCore\Menu\MenuRepository;
use Czim\CmsCore\Support\Data\MenuPresence;
use Czim\CmsCore\Test\TestCase;
use Illuminate\Support\Collection;

class MenuRepositoryTest extends TestCase
{

    /**
     * Testing menu layout config.
     *
     * @var array
     */
    protected $menuLayoutConfig = [];

    /**
     * @var MenuPresenceInterface[]
     */
    protected $menuLayoutStandard = [];

    /**
     * @var MenuPresenceInterface[]
     */
    protected $menuLayoutStandardFiltered = [];

    /**
     * Whether user should be mocked as admin.
     *
     * @var bool
     */
    protected $isAdmin = false;


    /**
     * @test
     */
    function it_initializes_succesfully()
    {
        $menu = $this->makeMenuRepository();

        $menu->initialize();
    }

    /**
     * @test
     */
    function it_only_initializes_once()
    {
        $menu = new MenuRepository(
            $this->getMockCore(true),
            $this->getMockAuth(),
            $this->getMockConfigInterpreter(),
            $this->getMockPermissionsFilter()
        );

        $menu->initialize();

        $menu->initialize();
    }

    /**
     * @test
     */
    function it_can_be_set_to_ignore_permissions_for_initialization()
    {
        $this->menuLayoutStandardFiltered = [
            $this->getMockBuilder(MenuPresence::class)->getMock()
        ];

        $menu = $this->makeMenuRepository();

        $menu->ignorePermission();

        $menu->initialize();

        // Assert that the filtered version is not used
        $layout = $menu->getMenuLayout();

        static::assertEmpty($layout, 'Ignoring permissions should not return filtered standard layout');
    }

    /**
     * @test
     */
    function it_returns_empty_layout_before_initialization()
    {
        $menu = $this->makeMenuRepository();

        $presences = $menu->getMenuLayout();

        static::assertInstanceOf(Collection::class, $presences);
        static::assertTrue($presences->isEmpty());
    }

    /**
     * @test
     */
    function it_returns_empty_alternative_presences_before_initialization()
    {
        $menu = $this->makeMenuRepository();

        $presences = $menu->getAlternativePresences();

        static::assertInstanceOf(Collection::class, $presences);
        static::assertTrue($presences->isEmpty());
    }

    // ------------------------------------------------------------------------------
    //      Cache
    // ------------------------------------------------------------------------------

    /**
     * @test
     */
    function it_clears_cached_menu_data()
    {
        $cacheMock = $this->getMockBuilder(CacheInterface::class)->getMock();

        $cacheMock->expects(static::exactly(2))
            ->method('forget')
            ->with(static::logicalOr(
                MenuRepository::CACHE_KEY_LAYOUT,
                MenuRepository::CACHE_KEY_INDEX
            ));

        $menu = new MenuRepository(
            $this->getMockCore(false, $cacheMock),
            $this->getMockAuth(),
            $this->getMockConfigInterpreter(),
            $this->getMockPermissionsFilter()
        );

        $menu->clearCache();
    }

    
    // ------------------------------------------------------------------------------
    //      Helpers
    // ------------------------------------------------------------------------------

    /**
     * @return MenuRepository
     */
    protected function makeMenuRepository()
    {
        return new MenuRepository(
            $this->getMockCore(),
            $this->getMockAuth(),
            $this->getMockConfigInterpreter(),
            $this->getMockPermissionsFilter()
        );
    }

    /**
     * @param bool $exactExpects
     * @param CacheInterface|null $cacheMock
     * @return CoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockCore($exactExpects = false, $cacheMock = null)
    {
        $mock = $this->getMockBuilder(CoreInterface::class)->getMock();

        $mock->expects($exactExpects ? static::exactly(5) : static::any())
             ->method('moduleConfig')
             ->willReturnCallback(function ($key, $default = null) {
                 switch ($key) {

                     case 'menu.layout':
                         return [];

                     case 'menu.cache':
                         return false;
                 }

                 return $default;
             });

        if ($cacheMock) {
            $mock->method('cache')->willreturn($cacheMock);
        }

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|AuthenticatorInterface
     */
    protected function getMockAuth()
    {
        $mock = $this->getMockBuilder(AuthenticatorInterface::class)->getMock();

        $mock->method('admin')->willReturn($this->isAdmin);

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|MenuConfigInterpreterInterface
     */
    protected function getMockConfigInterpreter()
    {
        $mock = $this->getMockBuilder(MenuConfigInterpreterInterface::class)->getMock();

        $mock->method('interpretLayout')->willReturn($this->getMockLayoutData());

        return $mock;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|MenuPermissionsFilterInterface
     */
    protected function getMockPermissionsFilter()
    {
        $mock = $this->getMockBuilder(MenuPermissionsFilterInterface::class)->getMock();

        $mock->method('buildPermissionsIndex')->willReturn($this->getMockIndex());
        $mock->method('filterLayout')->willReturn($this->getMockLayoutData(true));

        return $mock;
    }

    /**
     * @param bool $filtered
     * @return MenuLayoutDataInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockLayoutData($filtered = false)
    {
        $mock = $this->getMockBuilder(MenuLayoutDataInterface::class)->getMock();

        $layout = $filtered && $this->menuLayoutStandardFiltered
                ?   $this->menuLayoutStandardFiltered
                :   $this->menuLayoutStandard;

        $mock->method('layout')->willReturn($layout);
        $mock->method('setLayout')->willReturn($mock);
        $mock->method('alternative')->willReturn([]);
        $mock->method('setAlternative')->willReturn($mock);

        return $mock;
    }

    /**
     * @return MenuPermissionsIndexDataInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockIndex()
    {
        $mock = $this->getMockBuilder(MenuPermissionsIndexDataInterface::class)->getMock();

        return $mock;
    }


}
