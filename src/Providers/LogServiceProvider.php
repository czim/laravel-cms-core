<?php
namespace Czim\CmsCore\Providers;

use Illuminate\Contracts\Logging\Log;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Log\Writer;
use Illuminate\Support\ServiceProvider;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Logger\Formatters\CmsFormatterDecorator;
use Czim\CmsCore\Support\Enums\Component;
use Psr\Log\LoggerInterface;

class LogServiceProvider extends ServiceProvider
{

    public function boot()
    {
    }


    public function register()
    {
        $this->registerLogger();
    }

    /**
     * @return $this
     */
    protected function registerLogger()
    {
        $this->app->instance(Component::LOG, new Writer(
            $log = new Logger("cms.{$this->app->environment()}")
        ));

        // Clone all handlers defined for the main app, but decorate\
        // the formatters to include a prefix
        foreach ($this->getAppLog()->getMonolog()->getHandlers() as $handler) {
            /** @var HandlerInterface $handler */
            $log->pushHandler($handler = clone $handler);

            $handler
                ->setFormatter(new CmsFormatterDecorator($handler->getFormatter()))
                ->setLevel($this->getCore()->config('log.threshold'));
        }

        $this->app
            ->when(Handler::class)
            ->needs(LoggerInterface::class)
            ->give(Component::LOG);

        return $this;
    }

    /**
     * @return Log
     */
    protected function getAppLog()
    {
        return $this->app['log'];
    }

    /**
     * @return CoreInterface
     */
    protected function getCore()
    {
        return $this->app[Component::CORE];
    }

}
