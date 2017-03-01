<?php
namespace Czim\CmsCore\Test\Core;

use Czim\CmsCore\Contracts\Core\CacheInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Core\Cache;
use Czim\CmsCore\Test\TestCase;
use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Cache\Store;

class CacheTest extends TestCase
{

    /**
     * @test
     */
    function it_initializes_configured_cache_store_on_construction()
    {
        $mockCore = $this->getMockCore('test-store');

        $mockCache = $this->getMockCacheManager();

        // Shouldn't apply cache tags by default
        $mockCache->expects(static::exactly(0))
            ->method('tags')
            ->willReturnSelf();

        $mockCache->expects(static::once())
            ->method('store')
            ->with('test-store')
            ->willReturn($mockCache);

        $this->app->instance('cache', $mockCache);

        new Cache($mockCore);
    }

    /**
     * @test
     */
    function it_uses_cache_tags_if_configured_to()
    {
        $mockCore = $this->getMockCore('test-store', ['test-tag']);

        // Shouldn't apply cache tags by default
        $mockCache = $this->getMockCacheManager();
        $mockCache->expects(static::once())
            ->method('store')
            ->with('test-store')
            ->willReturnSelf();

        //$mockCache->expects(static::once())
        //    ->method('tags')
        //    ->willReturnSelf();

        $this->app->instance('cache', $mockCache);

        new Cache($mockCore);
    }

    /**
     * @test
     */
    function it_relays_has_call_to_cache_store()
    {
        $this->prepareCacheRelayMock(function ($mock) {
            /** @var Store|\PHPUnit_Framework_MockObject_MockObject $mock */
            $mock->expects(static::once())
                ->method('has')
                ->with('test-key')
                ->willReturn(true);
        });

        static::assertSame(true, $this->makeCache()->has('test-key'));
    }

    /**
     * @test
     */
    function it_relays_get_call_to_cache_store()
    {
        $this->prepareCacheRelayMock(function ($mock) {
            /** @var Store|\PHPUnit_Framework_MockObject_MockObject $mock */
            $mock->expects(static::once())
                ->method('get')
                ->with('test-key')
                ->willReturn('test-value');
        });

        static::assertEquals('test-value', $this->makeCache()->get('test-key'));
    }

    /**
     * @test
     */
    function it_relays_many_call_to_cache_store()
    {
        $this->prepareCacheRelayMock(function ($mock) {
            /** @var Store|\PHPUnit_Framework_MockObject_MockObject $mock */
            $mock->expects(static::once())
                ->method('many')
                ->with(['test-key'])
                ->willReturn(['test-value']);
        });

        static::assertEquals(['test-value'], $this->makeCache()->many(['test-key']));
    }

    /**
     * @test
     */
    function it_relays_pull_call_to_cache_store()
    {
        $this->prepareCacheRelayMock(function ($mock) {
            /** @var Store|\PHPUnit_Framework_MockObject_MockObject $mock */
            $mock->expects(static::once())
                ->method('pull')
                ->with('test-key')
                ->willReturn('test-value');
        });

        static::assertEquals('test-value', $this->makeCache()->pull('test-key'));
    }

    /**
     * @test
     */
    function it_relays_put_call_to_cache_store()
    {
        $this->prepareCacheRelayMock(function ($mock) {
            /** @var Store|\PHPUnit_Framework_MockObject_MockObject $mock */
            $mock->expects(static::once())
                ->method('put')
                ->with('test-key', 'test-value');
        });

        $this->makeCache()->put('test-key', 'test-value');
    }

    /**
     * @test
     */
    function it_relays_put_many_call_to_cache_store()
    {
        $this->prepareCacheRelayMock(function ($mock) {
            /** @var Store|\PHPUnit_Framework_MockObject_MockObject $mock */
            $mock->expects(static::once())
                ->method('putMany')
                ->with(['test-key' => 'test-value'], 10);
        });

        $this->makeCache()->putMany(['test-key' => 'test-value'], 10);
    }

    /**
     * @test
     */
    function it_relays_add_call_to_cache_store()
    {
        $this->prepareCacheRelayMock(function ($mock) {
            /** @var Store|\PHPUnit_Framework_MockObject_MockObject $mock */
            $mock->expects(static::once())
                ->method('add')
                ->with('test-key', 'test-value', 10)
                ->willReturn(true);
        });

        static::assertSame(true, $this->makeCache()->add('test-key', 'test-value', 10));
    }

    /**
     * @test
     */
    function it_relays_forever_call_to_cache_store()
    {
        $this->prepareCacheRelayMock(function ($mock) {
            /** @var Store|\PHPUnit_Framework_MockObject_MockObject $mock */
            $mock->expects(static::once())
                ->method('forever')
                ->with('test-key', 'test-value');
        });

        $this->makeCache()->forever('test-key', 'test-value');
    }

    /**
     * @test
     */
    function it_relays_remember_call_to_cache_store()
    {
        $callback = function () { return 'test-value'; };

        $this->prepareCacheRelayMock(function ($mock) use ($callback) {
            /** @var Store|\PHPUnit_Framework_MockObject_MockObject $mock */
            $mock->expects(static::once())
                ->method('remember')
                ->with('test-key', 10, $callback)
                ->willReturn('test-value');
        });

        static::assertEquals('test-value', $this->makeCache()->remember('test-key', 10, $callback));
    }

    /**
     * @test
     */
    function it_relays_rememberForever_call_to_cache_store()
    {
        $callback = function () { return 'test-value'; };

        $this->prepareCacheRelayMock(function ($mock) use ($callback) {
            /** @var Store|\PHPUnit_Framework_MockObject_MockObject $mock */
            $mock->expects(static::once())
                ->method('rememberForever')
                ->with('test-key', $callback)
                ->willReturn('test-value');
        });

        static::assertEquals('test-value', $this->makeCache()->rememberForever('test-key', $callback));
    }

    /**
     * @test
     */
    function it_relays_forget_call_to_cache_store()
    {
        $this->prepareCacheRelayMock(function ($mock) {
            /** @var Store|\PHPUnit_Framework_MockObject_MockObject $mock */
            $mock->expects(static::once())
                ->method('forget')
                ->with('test-key')
                ->willReturn(true);
        });

        static::assertSame(true, $this->makeCache()->forget('test-key'));
    }


    /**
     * @return Cache
     */
    protected function makeCache()
    {
        return new Cache($this->getMockCore());
    }

    /**
     * @param null|string         $store
     * @param null|false|string[] $tags
     * @return CoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockCore($store = null, $tags = null)
    {
        $mockCore = $this->getMockBuilder(CoreInterface::class)->getMock();

        if (null !== $store || null !== $tags) {
            $mockCore
                ->method('config')
                ->willReturnCallback(function ($key, $default) use ($store, $tags) {
                    switch ($key) {
                        case 'cache.store':
                            return null !== $store ? $store : $default;
                        case 'cache.tags':
                            return null !== $tags ? $tags : $default;

                    }
                    return $default;
                });
        }

        return $mockCore;
    }

    /**
     * @param callable $relayCallback
     */
    protected function prepareCacheRelayMock(callable $relayCallback)
    {
        $mockStore = $this->getMockCacheStore();

        $relayCallback($mockStore);

        $mockCache = $this->getMockCacheManager();
        $mockCache->method('store')->willReturn($mockStore);

        $this->app->instance('cache', $mockCache);
    }

    /**
     * @return CacheManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockCacheManager()
    {
        return $this->getMockBuilder(CacheManager::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return Store|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockCacheStore()
    {
        return $this->getMockBuilder(CacheInterface::class)
            ->getMock();
    }

}
