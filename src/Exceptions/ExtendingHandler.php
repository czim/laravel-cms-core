<?php
namespace Czim\CmsCore\Exceptions;

use App\Exceptions\Handler as LaravelHandler;
use Exception;
use Psr\Log\LoggerInterface;

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
     * @param LoggerInterface $log
     */
    public function __construct(LoggerInterface $log)
    {
        parent::__construct($log);

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
    public function report(Exception $e)
    {
        if ($this->shouldReport($e)) {
            cms()->log($e);
        }
    }

}
