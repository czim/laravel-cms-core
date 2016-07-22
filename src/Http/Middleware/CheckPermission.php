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
     * @var AuthenticatorInterface
     */
    protected $auth;

    /**
     * @param AuthenticatorInterface $auth
     */
    public function __construct(AuthenticatorInterface $auth)
    {
        $this->auth = $auth;
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
            &&  ! $this->auth->admin()
            &&  ! $this->auth->can($permission)
        ) {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Permission denied.', 403);
            } else {
                /** @var CoreInterface $core */
                $core = app(Component::CORE);

                return redirect()->route( $core->prefixRoute(NamedRoute::HOME) );
            }
        }

        return $next($request);
    }

}
