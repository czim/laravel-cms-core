<?php
namespace Czim\CmsCore\Contracts\Support\Localization;

interface LocaleRepositoryInterface
{

    /**
     * Returns the default locale.
     *
     * @return string
     */
    public function getDefault();

    /**
     * Determines and returns the available locales for the application.
     *
     * @return string[]
     */
    public function getAvailable();

    /**
     * Returns whether the application supports multiple locales.
     *
     * @return bool
     */
    public function isLocalized();

    /**
     * Returns whether a locale is valid & available.
     *
     * @param string $locale
     * @return bool
     */
    public function isAvailable($locale);

}
