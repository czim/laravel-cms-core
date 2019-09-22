<?php
namespace Czim\CmsCore\Contracts\Support\Localization;

interface LocaleRepositoryInterface
{

    /**
     * Returns the default locale.
     *
     * @return string
     */
    public function getDefault(): string;

    /**
     * Determines and returns the available locales for the application.
     *
     * @return string[]
     */
    public function getAvailable(): array;

    /**
     * Returns whether the application supports multiple locales.
     *
     * @return bool
     */
    public function isLocalized(): bool;

    /**
     * Returns whether a locale is valid & available.
     *
     * @param string $locale
     * @return bool
     */
    public function isAvailable(string $locale): bool;

}
