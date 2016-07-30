<?php
namespace Czim\CmsCore\Http\Middleware;

use Closure;
use Czim\CmsCore\Contracts\Core\CoreInterface;

/**
 * Class InitializeMenu
 *
 * Initialize the menu straigth away, so it is easier to debug
 * using the backtrace, which is a pain for view composers.
 */
class InitializeMenu
{

    /**
     * @var CoreInterface
     */
    protected $core;

    /**
     * @param CoreInterface $core
     */
    public function __construct(CoreInterface $core)
    {
        $this->core = $core;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param Closure                  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->core->menu()->initialize();

        return $next($request);
    }

}
