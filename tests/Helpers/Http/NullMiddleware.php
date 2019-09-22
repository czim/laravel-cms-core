<?php
namespace Czim\CmsCore\Test\Helpers\Http;

use Closure;
use Illuminate\Http\Request;

/**
 * Class NullMiddleware
 *
 * A middleware class that does nothing.
 */
class NullMiddleware
{

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }

}
