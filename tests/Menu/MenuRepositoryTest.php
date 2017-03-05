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
use Czim\CmsCore\Test\CmsBootTestCase;
use Illuminate\Support\Collection;

class MenuRepositoryTest extends CmsBootTestCase
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
     * Whether to mock enable caching.
     *
     * @var bool
     */
    protected $cacheEnabled = false;

    public function setUp()
    {
        parent::setUp();

        $this->cacheEnabled = false;
    }


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
    function it_caches_if_allowed()
    {
        $this->cacheEnabled = true;

        // Make a mock cache that returns test mocks
        $cacheMock = $this->getMockBuilder(CacheInterface::class)->getMock();

        $cacheMock->expects(static::atLeast(2))
            ->method(static::logicalOr('forever', 'put'))
            ->with(static::logicalOr(
                MenuRepository::CACHE_KEY_LAYOUT,
                MenuRepository::CACHE_KEY_INDEX
            ))
            ->willReturn(true);

        $menu = $this->makeMenuRepository($cacheMock);

        $menu->initialize();
    }

    /**
     * @test
     */
    function it_uses_cache_if_allowed_and_the_cache_is_set()
    {
        $this->cacheEnabled = true;

        $layoutMock = $this->getMockBuilder(MenuLayoutDataInterface::class)->getMock();
        $indexMock  = $this->getMockBuilder(MenuPermissionsIndexDataInterface::class)->getMock();

        // Make a mock cache that returns test mocks
        $cacheMock = $this->getMockBuilder(CacheInterface::class)->getMock();

        $cacheMock->expects(static::atLeast(2))
            ->method('has')
            ->with(static::logicalOr(
                MenuRepository::CACHE_KEY_LAYOUT,
                MenuRepository::CACHE_KEY_INDEX
            ))
            ->willReturn(true);

        $cacheMock->expects(static::atLeast(2))
            ->method('get')
            ->with(static::logicalOr(
                MenuRepository::CACHE_KEY_LAYOUT,
                MenuRepository::CACHE_KEY_INDEX
            ))
            ->willReturnCallback(function ($key) use ($layoutMock, $indexMock) {
                switch ($key) {
                    case MenuRepository::CACHE_KEY_LAYOUT:
                        return $layoutMock;
                    case MenuRepository::CACHE_KEY_INDEX:
                        return $indexMock;
                }
                return null;
            });

        $menu = $this->makeMenuRepository($cacheMock);

        $menu->initialize();
    }

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

        $menu = $this->makeMenuRepository($cacheMock);

        $menu->clearCache();
    }

    /**
     * @test
     */
    function it_catches_and_does_not_rethrow_exceptions_when_retrieving_cached_data()
    {
        $this->cacheEnabled = true;

        $cacheMock = $this->getMockBuilder(CacheInterface::class)->getMock();

        $cacheMock->method('has')->willReturn(true);

        $cacheMock->expects(static::atLeast(2))
            ->method('get')
            ->with(static::logicalOr(
                MenuRepository::CACHE_KEY_LAYOUT,
                MenuRepository::CACHE_KEY_INDEX
            ))
            ->willReturnCallback(function ($key) {
                switch ($key) {
                    case MenuRepository::CACHE_KEY_LAYOUT:
                        throw new \Exception('This test exception should be caught (layout)');
                    case MenuRepository::CACHE_KEY_INDEX:
                        throw new \Exception('This test exception should be caught (index)');
                }
                return null;
            });

        // The cache should be cleared on errors detected
        $cacheMock->expects(static::atLeast(2))
            ->method('forget')
            ->with(static::logicalOr(
                MenuRepository::CACHE_KEY_LAYOUT,
                MenuRepository::CACHE_KEY_INDEX
            ));

        $menu = $this->makeMenuRepository($cacheMock);

        $menu->initialize();
    }

    /**
     * @test
     */
    function it_does_not_throw_an_exception_if_cached_data_is_invalid()
    {
        $this->cacheEnabled = true;

        $cacheMock = $this->getMockBuilder(CacheInterface::class)->getMock();

        $cacheMock->method('has')->willReturn(true);

        $cacheMock->expects(static::atLeast(2))
            ->method('get')
            ->with(static::logicalOr(
                MenuRepository::CACHE_KEY_LAYOUT,
                MenuRepository::CACHE_KEY_INDEX
            ))
            ->willReturnCallback(function ($key) {
                switch ($key) {
                    case MenuRepository::CACHE_KEY_LAYOUT:
                        return 'invalid data';
                    case MenuRepository::CACHE_KEY_INDEX:
                        return 'invalid data';
                }
                return null;
            });

        // The cache should be cleared on errors detected
        $cacheMock->expects(static::atLeast(2))
            ->method('forget')
            ->with(static::logicalOr(
                MenuRepository::CACHE_KEY_LAYOUT,
                MenuRepository::CACHE_KEY_INDEX
            ));

        $menu = $this->makeMenuRepository($cacheMock);

        $menu->initialize();
    }

    
    // ------------------------------------------------------------------------------
    //      Helpers
    // ------------------------------------------------------------------------------

    /**
     * @param CacheInterface|null $cacheMock
     * @return MenuRepository
     */
    protected function makeMenuRepository($cacheMock = null)
    {
        return new MenuRepository(
            $this->getMockCore(false, $cacheMock),
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
                         return $this->cacheEnabled;
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
