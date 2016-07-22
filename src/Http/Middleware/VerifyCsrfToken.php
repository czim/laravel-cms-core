<?php
namespace Czim\CmsCore\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;
use Illuminate\Session\TokenMismatchException;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [];

    /**
     * Overridden to catch exception and redirect to login instead.
     *
     * {@inheritdoc}
     */
    public function handle($request, Closure $next)
    {
        try {

            return parent::handle($request, $next);

        } catch (TokenMismatchException $e) {

            return redirect()->back()->withInput()->with('token', csrf_token());
        }
    }

}
