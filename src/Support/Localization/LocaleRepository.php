<?php
namespace Czim\CmsCore\Support\Localization;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Support\Localization\LocaleRepositoryInterface;

class LocaleRepository implements LocaleRepositoryInterface
{
    /**
     * @var CoreInterface
     */
    protected $core;


    public function __construct(CoreInterface $core)
    {
        $this->core = $core;
    }


    /**
     * Returns the default locale.
     *
     * @return string
     */
    public function getDefault(): string
    {
        return $this->core->config('locale.default') ?: config('app.locale') ?: config('app.fallback_locale');
    }

    /**
     * Determines and returns the available locales for the application.
     *
     * @return string[]
     */
    public function getAvailable(): array
    {
        // If explicitly set in CMS config, this overrules all else
        $configLocales = $this->core->config('locale.available');

        if (is_array($configLocales)) {
            return $configLocales;
        }

        // Check if the localization package is used, and get locales from it
        $localizationLocales = $this->getLocalesForMcamaraLocalization();

        if ($localizationLocales) {
            return $localizationLocales;
        }

        // Alternatively, check if translatable package locales are available
        $localizationLocales = $this->getLocalesForTranslatable();

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
    public function isLocalized(): bool
    {
        return count($this->getAvailable()) > 1;
    }

    /**
     * Returns whether a locale is valid & available.
     *
     * @param string $locale
     * @return bool
     */
    public function isAvailable(string $locale): bool
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

    /**
     * Returns locales configured for translatable package, if any.
     *
     * @return string[]|false   false if package not used, or no locales configured
     */
    protected function getLocalesForTranslatable()
    {
        $locales = config('translatable.locales', false);

        if ( ! is_array($locales)) {
            return false;
        }

        return $locales;
    }

}
