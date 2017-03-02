<?php
namespace Czim\CmsCore\Contracts\Core;

use Czim\CmsCore\Contracts\Api\ApiCoreInterface;
use Czim\CmsCore\Contracts\Auth\AclRepositoryInterface;
use Illuminate\Database\ConnectionInterface;
use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Contracts\Menu\MenuRepositoryInterface;
use Czim\CmsCore\Contracts\Modules\ModuleManagerInterface;
use Illuminate\Session\SessionInterface;
use Psr\Log\LoggerInterface;

interface CoreInterface
{

    /**
     * @return BootCheckerInterface
     */
    public function bootChecker();

    /**
     * @return AuthenticatorInterface
     */
    public function auth();

    /**
     * @return ApiCoreInterface
     */
    public function api();

    /**
     * @return CacheInterface
     */
    public function cache();

    /**
     * @return ModuleManagerInterface
     */
    public function modules();

    /**
     * @return MenuRepositoryInterface
     */
    public function menu();

    /**
     * @return AclRepositoryInterface
     */
    public function acl();

    /**
     * @return NotifierInterface
     */
    public function notifier();

    /**
     * With any parameters set, will record a CMS log entry.
     * Without parameters returns the CMS Logger instance.
     *
     * @param null|string|array $level   if $message is set as non-array, type, otherwise, if string, the message
     * @param null|string|array $message if an array and $type was the message, the extra data, otherwise the message
     * @param null|array        $extra   only used if neither $type nor $message are arrays
     * @return LoggerInterface
     */
    public function log($level = null, $message = null, $extra = null);

    /**
     * Returns the database connection that the CMS uses.
     *
     * @return ConnectionInterface
     */
    public function db();

    /**
     * Returns the session interface that the CMS uses.
     *
     * @return SessionInterface
     */
    public function session();

    /**
     * Returns CMS configuration value.
     *
     * @param string     $key
     * @param null|mixed $default
     * @return mixed
     */
    public function config($key, $default = null);

    /**
     * Returns CMS configuration value for modules and/or menu.
     *
     * @param string     $key
     * @param null|mixed $default
     * @return mixed
     */
    public function moduleConfig($key, $default = null);

    /**
     * Returns CMS configuration value for the API.
     *
     * @param string     $key
     * @param null|mixed $default
     * @return mixed
     */
    public function apiConfig($key, $default = null);

    /**
     * Generate a URL to a named CMS route. This ensures that the
     * route name starts with the configured web route name prefix.
     *
     * @param string $name
     * @param array  $parameters
     * @param bool   $absolute
     * @return string
     */
    public function route($name, $parameters = [], $absolute = true);

    /**
     * Prefixes a route name with the standard web CMS prefix, if required.
     *
     * @param string $name
     * @return string
     */
    public function prefixRoute($name);

    /**
     * Generate a URL to a named CMS API route. This ensures that the
     * route name starts with the configured API route name prefix.
     *
     * @param string $name
     * @param array  $parameters
     * @param bool   $absolute
     * @return string
     */
    public function apiRoute($name, $parameters = [], $absolute = true);

    /**
     * Prefixes a route name with the standard web CMS API prefix, if required.
     *
     * @param string $name
     * @return string
     */
    public function prefixApiRoute($name);

    /**
     * Returns CMS Core version number.
     *
     * @return string
     */
    public function version();

}
