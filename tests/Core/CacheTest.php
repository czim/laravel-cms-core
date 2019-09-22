<?php
/** @noinspection ReturnTypeCanBeDeclaredInspection */
/** @noinspection AccessModifierPresentedInspection */

namespace Czim\CmsCore\Test\Core;

use Czim\CmsCore\Contracts\Core\CacheInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Core\Cache;
use Czim\CmsCore\Test\Helpers\Spies\CacheSpy;
use Czim\CmsCore\Test\CmsBootTestCase;
use Illuminate\Cache\CacheManager;
use Illuminate\Contracts\Cache\Store;
use Mockery;
use Mockery\Mock;
use Mockery\MockInterface;

class CacheTest extends CmsBootTestCase
{

    /**
     * @test
     */
    function it_initializes_configured_cache_store_on_construction()
    {
        $mockCore = $this->getMockCore('test-store');

        $cacheSpy = new CacheSpy();

        $this->app->instance('cache', $cacheSpy);

        new Cache($mockCore);

        static::assertEquals(1, $cacheSpy->storeCalled);
        static::assertEquals('test-store', $cacheSpy->storeParameter);
        static::assertEquals(0, $cacheSpy->tagsCalled);
    }

    /**
     * @test
     */
    function it_uses_cache_tags_if_configured_to()
    {
        $mockCore = $this->getMockCore('test-store', ['test-tag']);

        $cacheSpy = new CacheSpy();

        $this->app->instance('cache', $cacheSpy);

        new Cache($mockCore);

        static::assertEquals(1, $cacheSpy->storeCalled);
        static::assertEquals('test-store', $cacheSpy->storeParameter);
        static::assertEquals(1, $cacheSpy->tagsCalled);
        static::assertEquals(['test-tag'], $cacheSpy->tagsParameter);
    }

    /**
     * @test
     */
    function it_relays_has_call_to_cache_store()
    {
        $this->prepareCacheRelayMock(function ($mock) {
            /** @var Store|Mock|MockInterface $mock */
            $mock->shouldReceive('has')->once()
                ->with('test-key')->andReturn(true);
        });

        static::assertTrue($this->makeCache()->has('test-key'));
    }

    /**
     * @test
     */
    function it_relays_get_call_to_cache_store()
    {
        $this->prepareCacheRelayMock(function ($mock) {
            /** @var Store|Mock|MockInterface $mock */
            $mock->shouldReceive('get')->once()
                ->with('test-key', null)->andReturn('test-value');
        });

        static::assertEquals('test-value', $this->makeCache()->get('test-key'));
    }

    /**
     * @test
     */
    function it_relays_many_call_to_cache_store()
    {
        $this->prepareCacheRelayMock(function ($mock) {
            /** @var Store|Mock|MockInterface $mock */
            $mock->shouldReceive('many')->once()
                ->with(['test-key'])->andReturn(['test-value']);
        });

        static::assertEquals(['test-value'], $this->makeCache()->many(['test-key']));
    }

    /**
     * @test
     */
    function it_relays_pull_call_to_cache_store()
    {
        $this->prepareCacheRelayMock(function ($mock) {
            /** @var Store|Mock|MockInterface $mock */
            $mock->shouldReceive('pull')->once()
                ->with('test-key', null)->andReturn('test-value');
        });

        static::assertEquals('test-value', $this->makeCache()->pull('test-key'));
    }

    /**
     * @test
     */
    function it_relays_put_call_to_cache_store()
    {
        $this->prepareCacheRelayMock(function ($mock) {
            /** @var Store|Mock|MockInterface $mock */
            $mock->shouldReceive('put')->once()
                ->with('test-key', 'test-value', null);
        });

        $this->makeCache()->put('test-key', 'test-value');
    }

    /**
     * @test
     */
    function it_relays_put_many_call_to_cache_store()
    {
        $this->prepareCacheRelayMock(function ($mock) {
            /** @var Store|Mock|MockInterface $mock */
            $mock->shouldReceive('putMany')->once()->with(['test-key' => 'test-value'], 10);
        });

        $this->makeCache()->putMany(['test-key' => 'test-value'], 10);
    }

    /**
     * @test
     */
    function it_relays_add_call_to_cache_store()
    {
        $this->prepareCacheRelayMock(function ($mock) {
            /** @var Store|Mock|MockInterface $mock */
            $mock->shouldReceive('add')->once()
                ->with('test-key', 'test-value', 10)->andReturn(true);
        });

        static::assertTrue($this->makeCache()->add('test-key', 'test-value', 10));
    }

    /**
     * @test
     */
    function it_relays_forever_call_to_cache_store()
    {
        $this->prepareCacheRelayMock(function ($mock) {
            /** @var Store|Mock|MockInterface $mock */
            $mock->shouldReceive('forever')->once()->with('test-key', 'test-value');
        });

        $this->makeCache()->forever('test-key', 'test-value');
    }

    /**
     * @test
     */
    function it_relays_remember_call_to_cache_store()
    {
        $callback = function () {
            return 'test-value';
        };

        $this->prepareCacheRelayMock(function ($mock) use ($callback) {
            /** @var Store|Mock|MockInterface $mock */
            $mock->shouldReceive('remember')->once()
                ->with('test-key', 10, $callback)->andReturn('test-value');
        });

        static::assertEquals('test-value', $this->makeCache()->remember('test-key', 10, $callback));
    }

    /**
     * @test
     */
    function it_relays_rememberForever_call_to_cache_store()
    {
        $callback = function () {
            return 'test-value';
        };

        $this->prepareCacheRelayMock(function ($mock) use ($callback) {
            /** @var Store|Mock|MockInterface $mock */
            $mock->shouldReceive('rememberForever')->once()
                ->with('test-key', $callback)->andReturn('test-value');
        });

        static::assertEquals('test-value', $this->makeCache()->rememberForever('test-key', $callback));
    }

    /**
     * @test
     */
    function it_relays_forget_call_to_cache_store()
    {
        $this->prepareCacheRelayMock(function ($mock) {
            /** @var Store|Mock|MockInterface $mock */
            $mock->shouldReceive('forget')->once()->with('test-key')->andReturn(true);
        });

        static::assertTrue($this->makeCache()->forget('test-key'));
    }


    protected function makeCache(): Cache
    {
        return new Cache($this->getMockCore());
    }

    /**
     * @param null|string         $store
     * @param null|false|string[] $tags
     * @return CoreInterface|Mock|MockInterface
     */
    protected function getMockCore(?string $store = null, $tags = null)
    {
        /** @var CoreInterface|Mock|MockInterface $mockCore */
        $mockCore = Mockery::mock(CoreInterface::class);

        $mockCore
            ->shouldReceive('config')
            ->andReturnUsing(function (string $key, $default = null) use ($store, $tags) {
                switch ($key) {

                    case 'debug':
                        return false;
                    case 'cache.store':
                        return $store ?? $default;
                    case 'cache.tags':
                        return $tags ?? $default;

                }
                return $default;
            });

        return $mockCore;
    }

    protected function prepareCacheRelayMock(callable $relayCallback): void
    {
        $mockStore = $this->getMockCacheStore();

        $relayCallback($mockStore);

        $mockCache = $this->getMockCacheManager();
        $mockCache->shouldReceive('store')->andReturn($mockStore);

        $this->app->instance('cache', $mockCache);
    }

    /**
     * @return CacheManager|Mock|MockInterface
     */
    protected function getMockCacheManager()
    {
        return Mockery::mock(CacheManager::class);
    }

    /**
     * @return Store|CacheInterface|Mock|MockInterface
     */
    protected function getMockCacheStore()
    {
        return Mockery::mock(CacheInterface::class);
    }

}

