<?php
namespace Czim\CmsCore\Support\Localization;

use Czim\CmsCore\Contracts\Support\Localization\LocaleRepositoryInterface;

class LocaleRepository implements LocaleRepositoryInterface
{

    /**
     * Returns the default locale.
     *
     * @return string
     */
    public function getDefault()
    {
        return config('app.locale') ?: config('app.fallback_locale');
    }

    /**
     * Determines and returns the available locales for the application.
     */
    public function getAvailable()
    {
        // Check if the localization package is used, and get locales from it
        $localizationLocales = $this->getLocalesForMcamaraLocalization();

        if ($localizationLocales) {
            return $localizationLocales;
        }

        // Fallback is to check whether the default locale is equal to the fallback locale
        // If it isn't, we still have two locales to provide, otherwise localization is disabled.
        return array_unique([
            config('app.locale'),
            config('app.fallback_locale')
        ]);
    }

    /**
     * Returns whether the application supports multiple locales.
     *
     * @return bool
     */
    public function isLocalized()
    {
        return (count($this->getAvailable()) > 1);
    }

    /**
     * Returns whether a locale is valid & available.
     *
     * @param string $locale
     * @return bool
     */
    public function isAvailable($locale)
    {
        return in_array($locale, $this->getAvailable());
    }

    /**
     * Returns locales provided by mcamara's localization package, if any.
     *
     * @return string[]|false   false if package not used, or no locales configured
     */
    protected function getLocalesForMcamaraLocalization()
    {
        $locales = config('laravellocalization.supportedLocales', false);

        if ( ! is_array($locales)) {
            return false;
        }

        return array_keys($locales);
    }
}
