<?php
namespace Czim\CmsCore\Core;

use Czim\CmsCore\Contracts\Api\ApiCoreInterface;
use Czim\CmsCore\Contracts\Auth\AclRepositoryInterface;
use Czim\CmsCore\Contracts\Core\BootCheckerInterface;
use Czim\CmsCore\Contracts\Core\NotifierInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\ConnectionInterface;
use Czim\CmsCore\Contracts\Auth\AuthenticatorInterface;
use Czim\CmsCore\Contracts\Core\CacheInterface;
use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Menu\MenuRepositoryInterface;
use Czim\CmsCore\Contracts\Modules\ModuleManagerInterface;
use Czim\CmsCore\Support\Enums\Component;
use Illuminate\Session\SessionInterface;
use Psr\Log\LoggerInterface;

class Core implements CoreInterface
{

    /**
     * The CMS Core version.
     *
     * @var string
     */
    const VERSION = '0.0.2';


    /**
     * @var Application
     */
    protected $app;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }


    /**
     * Returns CMS Core version number.
     *
     * @return string
     */
    public function version()
    {
        return static::VERSION;
    }


    /**
     * @return BootCheckerInterface
     */
    public function bootChecker()
    {
        return $this->app[Component::BOOTCHECKER];
    }

    /**
     * @return AuthenticatorInterface
     */
    public function auth()
    {
        return $this->app[Component::AUTH];
    }

    /**
     * @return ApiCoreInterface
     */
    public function api()
    {
        return $this->app[Component::API];
    }

    /**
     * @return CacheInterface
     */
    public function cache()
    {
        return $this->app[Component::CACHE];
    }

    /**
     * @return ModuleManagerInterface
     */
    public function modules()
    {
        return $this->app[Component::MODULES];
    }

    /**
     * @return MenuRepositoryInterface
     */
    public function menu()
    {
        return $this->app[Component::MENU];
    }

    /**
     * @return AclRepositoryInterface
     */
    public function acl()
    {
        return $this->app[Component::ACL];
    }

    /**
     * @return NotifierInterface
     */
    public function notifier()
    {
        return $this->app[Component::NOTIFIER];
    }

    /**
     * With any parameters set, will record a CMS log entry.
     * Without parameters returns the CMS Logger instance.
     *
     * @param null|string|array $level   if $message is set as non-array, type, otherwise, if string, the message
     * @param null|string|array $message if an array and $type was the message, the extra data, otherwise the message
     * @param null|array        $extra   only used if neither $type nor $message are arrays
     * @return LoggerInterface
     */
    public function log($level = null, $message = null, $extra = null)
    {
        /** @var LoggerInterface $logger */
        $logger = $this->app[Component::LOG];

        // If parameters are set, enter a log message before returning the logger
        if (null !== $level || null !== $message) {

            list($level, $message, $extra) = $this->normalizeMonologParameters($level, $message, $extra);

            $logger->log($level, $message, $extra);
        }

        return $logger;
    }

    /**
     * Normalizes typical monolog parameters to level, message, extra parameter set.
     *
     * @param null|string|array $level   if $message is set as non-array, type, otherwise, if string, the message
     * @param null|string|array $message if an array and $type was the message, the extra data, otherwise the message
     * @param array             $extra   only used if neither $type nor $message are arrays
     * @return array
     */
    protected function normalizeMonologParameters($level = null, $message = null, $extra = [])
    {
        // Normalize the parameters so they're translated into sensible categories
        if (null !== $level) {
            if (is_array($level)) {
                $extra = $level;
                $level = null;
            } elseif (null === $message || is_array($message)) {
                $extra   = is_array($message) ? $message : $extra;
                $message = $level;
                $level   = null;
            }
        }

        if (is_array($message)) {
            $extra   = $message;
            $message = null;
        }

        if ( ! empty($extra) && ! is_array($extra)) {
            $extra = (array) $extra;
        }

        if ($message instanceof \Exception) {
            $level = 'error';
        }

        if ( ! $extra) {
            $extra = [];
        }

        return [
            $level   ?: 'info',
            $message ?: 'Data.',
            $extra
        ];
    }


    /**
     * Returns the database connection that the CMS uses.
     *
     * @return ConnectionInterface
     */
    public function db()
    {
        return $this->app['db']->connection($this->config('database.driver'));
    }

    /**
     * Returns the session interface that the CMS uses.
     *
     * @return SessionInterface
     */
    public function session()
    {
        return $this->app['session']->driver($this->config('session.driver'));
    }

    /**
     * Returns CMS configuration value.
     *
     * @param string     $key
     * @param null|mixed $default
     * @return mixed
     */
    public function config($key, $default = null)
    {
        return config('cms-core.' . $key, $default);
    }

    /**
     * Returns CMS configuration value for modules and/or menu.
     *
     * @param string     $key
     * @param null|mixed $default
     * @return mixed
     */
    public function moduleConfig($key, $default = null)
    {
        return config('cms-modules.' . $key, $default);
    }

    /**
     * Returns CMS configuration value for the API.
     *
     * @param string     $key
     * @param null|mixed $default
     * @return mixed
     */
    public function apiConfig($key, $default = null)
    {
        return config('cms-api.' . $key, $default);
    }

    /**
     * Generate a URL to a named CMS route. This ensures that the
     * route name starts with the configured route name prefix.
     *
     * @param string $name
     * @param array  $parameters
     * @param bool   $absolute
     * @return string
     */
    public function route($name, $parameters = [], $absolute = true)
    {
        return app('url')->route($this->prefixRoute($name), $parameters, $absolute);
    }

    /**
     * Prefixes a route name with the standard CMS prefix, if required.
     *
     * @param string $name
     * @return string
     */
    public function prefixRoute($name)
    {
        $prefix = $this->config('route.name-prefix');

        return starts_with($name, $prefix) ? $name : $prefix . $name;
    }

    /**
     * Generate a URL to a named CMS API route. This ensures that the
     * route name starts with the configured API route name prefix.
     *
     * @param string $name
     * @param array  $parameters
     * @param bool   $absolute
     * @return string
     */
    public function apiRoute($name, $parameters = [], $absolute = true)
    {
        return app('url')->route($this->prefixApiRoute($name), $parameters, $absolute);
    }

    /**
     * Prefixes a route name with the standard web CMS API prefix, if required.
     *
     * @param string $name
     * @return string
     */
    public function prefixApiRoute($name)
    {
        $prefix = $this->apiConfig('route.name-prefix');

        return starts_with($name, $prefix) ? $name : $prefix . $name;
    }

}
