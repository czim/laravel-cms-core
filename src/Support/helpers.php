<?php

use Czim\CmsCore\Contracts\Api\ApiCoreInterface;
use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Core\NotifierInterface;
use Czim\CmsCore\Support\Enums\CmsMiddleware;
use Czim\CmsCore\Support\Enums\Component;
use Illuminate\Translation\Translator;

if ( ! function_exists('cms')) {
    /**
     * Get the available CMS core instance.
     *
     * @return CoreInterface
     */
    function cms(): CoreInterface
    {
        return app(Component::CORE);
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
    function cms_route($name, $parameters = [], $absolute = true): string
    {
        $name = app(Component::CORE)->prefixRoute($name);

        return app('url')->route($name, $parameters, $absolute);
    }
}

if ( ! function_exists('cms_auth')) {
    /**
     * Get the available CMS authenticator instance.
     *
     * @return AuthenticatorInterface
     */
    function cms_auth(): AuthenticatorInterface
    {
        return app(Component::AUTH);
    }
}

if ( ! function_exists('cms_api')) {
    /**
     * Get the available CMS API Core instance.
     *
     * @return ApiCoreInterface
     */
    function cms_api(): ApiCoreInterface
    {
        return app(Component::API);
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
        return app(Component::CORE)
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
    function cms_mw_permission(string $permission): string
    {
        return CmsMiddleware::PERMISSION . ':' . $permission;
    }
}

if ( ! function_exists('cms_flash')) {
    /**
     * Set a CMS flash message
     *
     * @param string      $message
     * @param null|string $level
     * @return NotifierInterface
     */
    function cms_flash(string $message, ?string $level = null): NotifierInterface
    {
        return app(Component::NOTIFIER)
            ->flash($message, $level);
    }
}

if ( ! function_exists('cms_trans')) {

    /**
     * Translate the given message using CMS translations.
     *
     * Falls back to application translations if CMS does not have the key.
     *
     * @param string      $id
     * @param array       $parameters
     * @param string      $domain
     * @param string|null $locale
     * @return string|null|\Illuminate\Contracts\Translation\Translator
     */
    function cms_trans(
        ?string $id = null,
        array $parameters = [],
        string $domain = 'messages',
        ?string $locale = null
    ) {

        /** @var Translator $translator */
        $translator = app('translator');

        if ($id === null) {
            return $translator;
        }

        $key = cms()->config('translation.prefix', 'cms::') . $id;

        // Fall back to non-cms translation if unavailable for CMS
        if ( ! $translator->has($key)) {
            $key = $id;
        }

        return $translator->get($key, $parameters, $domain, $locale);
    }
}

if ( ! function_exists('cms_trans_choice')) {

    /**
     * Translates the given message based on a count using CMS-translations.
     *
     * Falls back to application translations if CMS does not have the key.
     *
     * @param  string|null         $id
     * @param  int|array|Countable $number
     * @param  array               $parameters
     * @param  string              $domain
     * @param  string|null         $locale
     * @return string
     */
    function cms_trans_choice(
        ?string $id,
        $number,
        array $parameters = [],
        string $domain = 'messages',
        ?string $locale = null
    ): ?string {
        /** @var Translator $translator */
        $translator = app('translator');

        $key = cms()->config('translation.prefix', 'cms::') . $id;

        // Fall back to non-cms translation if unavailable for CMS
        if ( ! $translator->has($key)) {
            $key = $id;
        }

        return app('translator')->choice($key, $number, $parameters, $domain, $locale);
    }
}
