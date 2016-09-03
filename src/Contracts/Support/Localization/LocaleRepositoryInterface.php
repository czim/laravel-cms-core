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
     */
    public function getAvailable();

    /**
     * Returns whether the application supports multiple locales.
     *
     * @return bool
     */
    public function isLocalized();

}
