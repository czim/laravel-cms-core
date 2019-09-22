<?php
namespace Czim\CmsCore\Support\Translation;

use Illuminate\Support\Str;
use Illuminate\Translation\Translator;

/**
 * Class CmsTranslator
 *
 * Translator that uses CMS-specific translations where possible,
 * and falls back to application translations where not.
 */
class CmsTranslator extends Translator
{

    /**
     * Prefix to prepend to normal keys to get CMS-specific translations.
     *
     * @return string
     */
    protected function getCmsPrefix(): string
    {
        return 'cms::';
    }

    /**
     * Prefixes an unprefixed key to be CMS-specific.
     *
     * @param string $key
     * @return string
     */
    protected function applyCmsPrefix(string $key): string
    {
        return $this->getCmsPrefix() . $key;
    }

    /**
     * Returns whether a key is prefixed as CMS-specific.
     *
     * @param string $key
     * @return bool
     */
    protected function isCmsPrefixed(string $key): bool
    {
        return Str::startsWith($key, $this->getCmsPrefix());
    }

    /**
     * Get the translation for the given key.
     *
     * {@inheritdoc}
     */
    public function get($key, array $replace = [], $locale = null, $fallback = true)
    {
        // If we have a CMS-prefixed version, use it.
        if ( ! $this->isCmsPrefixed($key)) {
            $prefixedKey = $this->applyCmsPrefix($key);

            if (parent::has($prefixedKey, $locale, $fallback)) {
                $key = $prefixedKey;
            }
        }

        return parent::get($key, $replace, $locale, $fallback);
    }

}
