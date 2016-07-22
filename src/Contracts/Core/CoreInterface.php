<?php
namespace Czim\CmsCore\Contracts\Core;

use Illuminate\Database\ConnectionInterface;
use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Contracts\Menu\MenuInterface;
use Czim\CmsCore\Contracts\Modules\ManagerInterface;
use Psr\Log\LoggerInterface;

interface CoreInterface extends StateInterface
{

    /**
     * @return AuthenticatorInterface
     */
    public function auth();

    /**
     * @return CacheInterface
     */
    public function cache();

    /**
     * @return ManagerInterface
     */
    public function modules();

    /**
     * @return MenuInterface
     */
    public function menu();

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
     * Generate a URL to a named CMS route. This ensures that the
     * route name starts with the configured route name prefix.
     *
     * @param string $name
     * @param array  $parameters
     * @param bool   $absolute
     * @return string
     */
    public function route($name, $parameters = [], $absolute = true);

    /**
     * Prefixes a route name with the standard CMS prefix, if required.
     *
     * @param string $name
     * @return string
     */
    public function prefixRoute($name);

}
