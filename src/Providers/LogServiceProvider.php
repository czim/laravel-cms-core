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
        $log = new Logger("cms.{$this->app->environment()}");

        $writer = new Writer($log);

        // For separate logs, configure the writer to use a different file/path
        if ($this->getCore()->config('log.separate')) {
            $this->configureSeparateWriter($writer);
        }

        $this->app->instance(Component::LOG, $writer);

        // For shared logs, decorate the entries for the CMS
        if ( ! $this->getCore()->config('log.separate')) {

            // Clone all handlers defined for the main app, but decorate
            // the formatters to include a prefix
            foreach ($this->getAppLog()->getMonolog()->getHandlers() as $handler) {
                /** @var HandlerInterface $handler */
                $log->pushHandler($handler = clone $handler);

                $handler
                    ->setFormatter(new CmsFormatterDecorator($handler->getFormatter()))
                    ->setLevel($this->getCore()->config('log.threshold'));
            }
        }

        $this->bindLoggerContextually();

        return $this;
    }

    /**
     * Configures log writer for logging to files separately from the application.
     *
     * @param Writer $writer
     */
    protected function configureSeparateWriter(Writer $writer)
    {
        $path = 'logs/' . ltrim($this->getCore()->config('log.file', 'cms'), '/');

        $writer->useDailyFiles(storage_path($path));

        foreach ($writer->getMonolog()->getHandlers() as $handler) {

            $handler->setLevel($this->getCore()->config('log.threshold'));
        }
    }

    /**
     * Binds the CMS logger contextually for CMS requests.
     *
     * @return $this
     */
    protected function bindLoggerContextually()
    {
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
