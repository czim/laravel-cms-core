<?php
namespace Czim\CmsCore\Exceptions;

use App\Exceptions\Handler as LaravelHandler;
use Exception;

/**
 * Class ExtendingHandler
 *
 * This CMS handler extends the app's Handler.
 */
class ExtendingHandler extends LaravelHandler
{

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
