<?php

if ( ! function_exists('cms')) {
    /**
     * Get the available CMS core instance.
     *
     * @return \Czim\CmsCore\Contracts\Core\CoreInterface
     */
    function cms()
    {
        return app(\Czim\CmsCore\Support\Enums\Component::CORE);
    }
}

if ( ! function_exists('cms_route')) {
    /**
     * Generate a URL to a named CMS route. This ensures that the
     * route name starts with the configured route name prefix.
     *
     * @param  string  $name
     * @param  array   $parameters
     * @param  bool    $absolute
     * @return string
     */
    function cms_route($name, $parameters = [], $absolute = true)
    {
        $name = app(\Czim\CmsCore\Support\Enums\Component::CORE)->prefixRoute($name);

        return app('url')->route($name, $parameters, $absolute);
    }
}

if ( ! function_exists('cms_auth')) {
    /**
     * Get the available CMS authenticator instance.
     *
     * @return \Czim\CmsCore\Contracts\Auth\AuthenticatorInterface
     */
    function cms_auth()
    {
        return app(\Czim\CmsCore\Support\Enums\Component::AUTH);
    }
}

if ( ! function_exists('cms_api')) {
    /**
     * Get the available CMS API Core instance.
     *
     * @return \Czim\CmsCore\Contracts\Api\ApiCoreInterface
     */
    function cms_api()
    {
        return app(\Czim\CmsCore\Support\Enums\Component::API);
    }
}

if ( ! function_exists('cms_config')) {
    /**
     * Get / set the specified configuration value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param  array|string  $key
     * @param  mixed  $default
     * @return mixed
     */
    function cms_config($key = null, $default = null)
    {
        return app(\Czim\CmsCore\Support\Enums\Component::CORE)
            ->config($key, $default);
    }
}

if ( ! function_exists('cms_mw_permission')) {
    /**
     * Returns CMS middleware definition for a given permission
     *
     * @param string $permission
     * @return string
     */
    function cms_mw_permission($permission)
    {
        return \Czim\CmsCore\Support\Enums\CmsMiddleware::PERMISSION . ':' . $permission;
    }
}

if ( ! function_exists('cms_flash')) {
    /**
     * Set a CMS flash message
     *
     * @param string      $message
     * @param null|string $level
     */
    function cms_flash($message, $level = null)
    {
        return app(\Czim\CmsCore\Support\Enums\Component::NOTIFIER)
            ->flash($message, $level);
    }
}

if ( ! function_exists('cms_trans')) {

    /**
     * Translate the given message using CMS translations.
     *
     * Falls back to application translations if CMS does not have the key.
     *
     * @param  string  $id
     * @param  array   $parameters
     * @param  string  $domain
     * @param  string  $locale
     * @return string|null
     */
    function cms_trans($id = null, $parameters = [], $domain = 'messages', $locale = null)
    {
        /** @var \Illuminate\Translation\Translator $translator */
        $translator = app('translator');

        if (is_null($id)) {
            return $translator;
        }

        $key = cms()->config('translation.prefix', 'cms::') . $id;

        // Fall back to non-cms translation if unavailable for CMS
        if ( ! $translator->has($key)) {
            $key = $id;
        }

        return $translator->trans($key, $parameters, $domain, $locale);
    }
}

if ( ! function_exists('cms_trans_choice')) {

    /**
     * Translates the given message based on a count using CMS-translations.
     *
     * Falls back to application translations if CMS does not have the key.
     *
     * @param  string  $id
     * @param  int|array|\Countable  $number
     * @param  array   $parameters
     * @param  string  $domain
     * @param  string  $locale
     * @return string
     */
    function cms_trans_choice($id, $number, array $parameters = [], $domain = 'messages', $locale = null)
    {
        /** @var \Illuminate\Translation\Translator $translator */
        $translator = app('translator');

        $key = cms()->config('translation.prefix', 'cms::') . $id;

        // Fall back to non-cms translation if unavailable for CMS
        if ( ! $translator->has($key)) {
            $key = $id;
        }

        return app('translator')->transChoice($key, $number, $parameters, $domain, $locale);
    }
}
