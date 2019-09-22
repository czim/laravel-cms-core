<?php
namespace Czim\CmsCore\Contracts\Core;

use Czim\CmsCore\Contracts\Api\ApiCoreInterface;
use Czim\CmsCore\Contracts\Auth\AclRepositoryInterface;
use Czim\CmsCore\Contracts\Support\View\AssetManagerInterface;
use Illuminate\Database\ConnectionInterface;
use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Contracts\Menu\MenuRepositoryInterface;
use Czim\CmsCore\Contracts\Modules\ModuleManagerInterface;
use Illuminate\Contracts\Session\Session;
use Psr\Log\LoggerInterface;

interface CoreInterface
{

    public function bootChecker(): BootCheckerInterface;

    public function auth(): AuthenticatorInterface;

    public function api(): ApiCoreInterface;

    public function cache(): CacheInterface;

    public function modules(): ModuleManagerInterface;

    public function menu(): MenuRepositoryInterface;

    public function assets(): AssetManagerInterface;

    public function acl(): AclRepositoryInterface;

    public function notifier(): NotifierInterface;

    /**
     * With any parameters set, will record a CMS log entry.
     * Without parameters returns the CMS Logger instance.
     *
     * @param null|string|array $level   if $message is set as non-array, type, otherwise, if string, the message
     * @param null|string|array $message if an array and $type was the message, the extra data, otherwise the message
     * @param null|array        $extra   only used if neither $type nor $message are arrays
     * @return LoggerInterface
     */
    public function log($level = null, $message = null, $extra = null): LoggerInterface;

    /**
     * Returns the database connection that the CMS uses.
     *
     * @return ConnectionInterface
     */
    public function db();

    /**
     * Returns the session interface that the CMS uses.
     *
     * @return Session
     */
    public function session();

    /**
     * Returns CMS configuration value.
     *
     * @param string     $key
     * @param null|mixed $default
     * @return mixed
     */
    public function config(string $key, $default = null);

    /**
     * Returns CMS configuration value for modules and/or menu.
     *
     * @param string     $key
     * @param null|mixed $default
     * @return mixed
     */
    public function moduleConfig(string $key, $default = null);

    /**
     * Returns CMS configuration value for the API.
     *
     * @param string     $key
     * @param null|mixed $default
     * @return mixed
     */
    public function apiConfig(string $key, $default = null);

    /**
     * Generate a URL to a named CMS route. This ensures that the
     * route name starts with the configured web route name prefix.
     *
     * @param string $name
     * @param array  $parameters
     * @param bool   $absolute
     * @return string
     */
    public function route(string $name, array $parameters = [], bool $absolute = true): string;

    /**
     * Prefixes a route name with the standard web CMS prefix, if required.
     *
     * @param string $name
     * @return string
     */
    public function prefixRoute(string $name): string;

    /**
     * Generate a URL to a named CMS API route. This ensures that the
     * route name starts with the configured API route name prefix.
     *
     * @param string $name
     * @param array  $parameters
     * @param bool   $absolute
     * @return string
     */
    public function apiRoute(string $name, array $parameters = [], bool $absolute = true): string;

    /**
     * Prefixes a route name with the standard web CMS API prefix, if required.
     *
     * @param string $name
     * @return string
     */
    public function prefixApiRoute(string $name): string;

    /**
     * Returns CMS Core version number.
     *
     * @return string
     */
    public function version(): string;

}
