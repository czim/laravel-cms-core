<?php
namespace Czim\CmsCore\Test\Providers;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Providers\LogServiceProvider;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsCore\Test\TestCase;
use Illuminate\Log\Writer as IlluminateLogWriter;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;

class LogServiceProviderTest extends TestCase
{
    /**
     * @var array
     */
    protected $spyData = [];

    /**
     * @var array
     */
    protected $logConfig = [
        'log' => [
            'separate'  => false,
            'file'      => 'cms',
            'daily'     => true,
            'max_files' => null,
            'threshold' => 'debug',
        ]
    ];


    /**
     * @test
     */
    function it_writes_to_the_laravel_log_if_configured_to()
    {
        $this->resetSpyData();

        // Set up
        $mockCore = $this->getMockBuilder(CoreInterface::class)->getMock();
        $mockCore->expects(static::atLeastOnce())
            ->method('config')
            ->willReturnCallback(function ($key, $default = null) {
                return $this->spyCoreConfig($key, $default);
            });

        $mockFormatter = $this->getMockBuilder(FormatterInterface::class)->getMock();

        $mockHandler = $this->getMockBuilder(HandlerInterface::class)
            ->setMethods([
                'getFormatter', 'setFormatter', 'setLevel', 'isHandling',
                'handle', 'handleBatch', 'pushProcessor', 'popProcessor',
            ])
            ->getMock();
        $mockHandler->expects(static::atLeastOnce())->method('getFormatter')->willReturn($mockFormatter);
        $mockHandler->expects(static::atLeastOnce())->method('setFormatter')->willReturnSelf();
        $mockHandler->expects(static::atLeastOnce())->method('setLevel')->willReturn($mockFormatter);

        $mockMonolog = $this->getMockBuilder(Logger::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockMonolog->method('getHandlers')->willReturn([ $mockHandler ]);

        $mockLog = $this->getMockBuilder(IlluminateLogWriter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockLog->method('getMonolog')->willReturn($mockMonolog);

        $this->app->instance(Component::CORE, $mockCore);
        $this->app->instance('log', $mockLog);

        // Test
        $provider = new LogServiceProvider($this->app);
        $provider->register();

        static::assertGreaterThanOrEqual(1, array_get($this->spyData, 'log.separate', 0));
        static::assertGreaterThanOrEqual(1, array_get($this->spyData, 'log.threshold', 0));
        static::assertGreaterThanOrEqual(1, array_get($this->spyData, 'log.threshold', 0));
    }

    /**
     * @test
     */
    function it_writes_to_a_separate_log_file_if_configured_to()
    {
        $this->resetSpyData();

        array_set($this->logConfig, 'log.separate', true);

        // Set up
        $mockCore = $this->getMockBuilder(CoreInterface::class)->getMock();
        $mockCore->expects(static::atLeastOnce())
            ->method('config')
            ->willReturnCallback(function ($key, $default = null) {
                return $this->spyCoreConfig($key, $default);
            });

        $this->app->instance(Component::CORE, $mockCore);

        // Test
        $provider = new LogServiceProvider($this->app);
        $provider->register();

        static::assertGreaterThanOrEqual(1, array_get($this->spyData, 'log.separate', 0));
        static::assertGreaterThanOrEqual(1, array_get($this->spyData, 'log.file', 0));
    }


    /**
     * Resets spy data for config spy.
     */
    protected function resetSpyData()
    {
        $this->spyData = [];
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function spyCoreConfig($key, $default = null)
    {
        if ($counter = array_get($this->spyData, $key, 0)) {
            array_set($this->spyData, $key, ++$counter);
        } else {
            array_set($this->spyData, $key, 1);
        }

        return array_get($this->logConfig, $key, $default);
    }

}
