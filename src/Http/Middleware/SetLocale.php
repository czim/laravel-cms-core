<?php
namespace Czim\CmsCore\Http\Middleware;

use Closure;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Support\Localization\LocaleRepositoryInterface;
use Illuminate\Http\Request;

/**
 * Class SetLocale
 *
 * Sets the active locale for localized applications based on session content
 * or the request locale.
 */
class SetLocale
{

    /**
     * @var CoreInterface
     */
    protected $core;

    /**
     * @var LocaleRepositoryInterface
     */
    protected $repository;


    public function __construct(CoreInterface $core, LocaleRepositoryInterface $repository)
    {
        $this->core       = $core;
        $this->repository = $repository;
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
        if ( ! $this->repository->isLocalized()) {
            return $next($request);
        }

        $locale = $this->getValidSessionLocale();

        if ( ! $locale && $this->core->config('locale.request-based')) {
            $locale = $this->getRequestLocale($request);
        }

        if ($locale) {
            app()->setLocale($locale);
        }

        return $next($request);
    }

    /**
     * Returns the session locale, replacing unavailable with default.
     *
     * @return bool|string
     */
    protected function getValidSessionLocale()
    {
        $locale = $this->getSessionLocale();

        if ( ! $locale) {
            return false;
        }

        if ( ! $this->repository->isAvailable($locale)) {
            $locale = $this->repository->getDefault();
        }

        return $locale;
    }

    protected function getSessionLocale(): ?string
    {
        $sessionKey = $this->core->config('session.prefix') . 'locale';

        return session($sessionKey);
    }

    /**
     * @param Request $request
     * @return string
     */
    protected function getRequestLocale($request): string
    {
        $locales = array_unique(
            array_merge(
                [ $this->repository->getDefault() ],
                $this->repository->getAvailable()
            )
        );

        return $request->getPreferredLanguage($locales);
    }

}
