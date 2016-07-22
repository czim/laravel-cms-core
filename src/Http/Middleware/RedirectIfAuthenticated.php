<?php
namespace Czim\CmsCore\Http\Middleware;

use Closure;
use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Support\Enums\Component;
use Czim\CmsCore\Support\Enums\NamedRoute;

class RedirectIfAuthenticated
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
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->auth->check()) {
            /** @var CoreInterface $core */
            $core = app(Component::CORE);

            return redirect()->route( $core->prefixRoute(NamedRoute::HOME) );
        }

        return $next($request);
    }

}
