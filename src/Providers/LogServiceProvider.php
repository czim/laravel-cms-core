<?php
namespace Czim\CmsCore\Providers;

use Illuminate\Contracts\Logging\Log;
use Illuminate\Log\Writer;
use Illuminate\Support\ServiceProvider;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Support\Logging\CmsFormatterDecorator;
use Czim\CmsCore\Support\Enums\Component;

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

        // Bind dedicated log writer for the CMS
        $writer = new Writer($log);
        $this->app->instance(Component::LOG, $writer);


        if ($this->getCore()->config('log.separate')) {
            // For separate logs, configure the writer to use a different file/path

            $this->configureSeparateWriter($writer);

        } else {
            // For shared logs, decorate the entries for the CMS

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

        if ($this->getCore()->config('log.daily')) {
            $writer->useDailyFiles(
                storage_path($path),
                (int) $this->getCore()->config('log.max_files')
            );
        }

        foreach ($writer->getMonolog()->getHandlers() as $handler) {

            $handler->setLevel($this->getCore()->config('log.threshold'));
        }
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
