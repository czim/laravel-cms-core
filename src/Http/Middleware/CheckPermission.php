<?php
namespace Czim\CmsCore\Http\Middleware;

use Closure;
use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsCore\Support\Enums\NamedRoute;

class CheckPermission
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
     * @param string|null              $permission
     * @return mixed
     */
    public function handle($request, Closure $next, $permission = null)
    {
        if (    $permission
            &&  ! $this->core->auth()->admin()
            &&  ! $this->core->auth()->can($permission)
        ) {

            if ($this->core->bootChecker()->isCmsApiRequest()) {
                return $this->core->api()->error('Permission denied', 403);
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response('Permission denied.', 403);
            }

            /** @var CoreInterface $core */
            $core = app(Component::CORE);

            return redirect()->route( $core->prefixRoute(NamedRoute::HOME) );
        }

        return $next($request);
    }

}
