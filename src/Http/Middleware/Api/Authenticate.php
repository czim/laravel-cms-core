<?php
namespace Czim\CmsCore\Http\Middleware\Api;

use Closure;
use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;

class Authenticate
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
        if ( ! $this->auth->check()) {
            return cms_api()->response('Unauthorized.', 401);
        }

        return $next($request);
    }

}
