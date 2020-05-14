<?php
namespace Czim\CmsCore\Exceptions;

use App\Exceptions\Handler as LaravelHandler;
use Illuminate\Contracts\Container\Container;
use Throwable;

/**
 * Class ExtendingHandler
 *
 * This CMS handler extends the app's Handler.
 */
class ExtendingHandler extends LaravelHandler
{

    /**
     * Create a new exception handler instance.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        parent::__construct($container);

        $this->mergeConfiguredDontReportExceptions();
    }

    /**
     * Merges CMS-configured exception classes not to report
     */
    protected function mergeConfiguredDontReportExceptions()
    {
        $this->dontReport = array_merge(
            $this->dontReport,
            config('cms-core.log.dont-report', [])
        );
    }

    /**
     * Override to report to CMS log
     *
     * {@inheritdoc}
     */
    public function report(Throwable $e)
    {
        if ($this->shouldReport($e)) {
            cms()->log($e);
        }
    }

}
