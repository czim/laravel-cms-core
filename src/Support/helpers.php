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
        $prefix = app(\Czim\CmsCore\Support\Enums\Component::CORE)
            ->config('route.name-prefix');

        $name = starts_with($name, $prefix) ? $name : $prefix . $name;

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

//if ( ! function_exists('cms_asset')) {
//    /**
//     * Generate an asset path for the CMS.
//     *
//     * @param  string  $path
//     * @param  bool    $secure
//     * @return string
//     */
//    function cms_asset($path, $secure = null)
//    {
//        return app('url')->asset($path, $secure);
//    }
//}

//if ( ! function_exists('cms_secure_asset')) {
//    /**
//     * Generate an CMS asset path for the CMS.
//     *
//     * @param  string  $path
//     * @return string
//     */
//    function cms_secure_asset($path)
//    {
//        return asset($path, true);
//    }
//}
