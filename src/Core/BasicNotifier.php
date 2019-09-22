<?php
namespace Czim\CmsCore\Core;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Czim\CmsCore\Contracts\Core\NotifierInterface;

/**
 * Class BasicNotifier
 *
 * Basic session based flash message handling. Ignores notifications.
 */
class BasicNotifier implements NotifierInterface
{

    /**
     * The session key for storing flash messages
     */
    public const SESSION_FLASH_KEY = 'flash-messages';

    /**
     * The default message level
     */
    public const DEFAULT_LEVEL = 'info';

    /**
     * @var CoreInterface
     */
    protected $core;


    /**
     * @param CoreInterface $core
     */
    public function __construct(CoreInterface $core)
    {
        $this->core = $core;
    }


    /**
     * Flashes a message to the session.
     *
     * @param string      $message
     * @param null|string $level
     * @return $this
     */
    public function flash(string $message, ?string $level = null): NotifierInterface
    {
        if (null === $level) {
            $level = static::DEFAULT_LEVEL;
        }

        $messages = $this->core->session()->get(static::SESSION_FLASH_KEY, []);

        $messages[] = [
            'message' => $message,
            'level'   => $level,
        ];

        $this->core->session()->put(static::SESSION_FLASH_KEY, $messages);

        return $this;
    }

    /**
     * Returns any previously flashed messages.
     *
     * @param bool $clear whether to delete the flashed messages from the session
     * @return array set of associative arrays with message, level keys
     */
    public function getFlashed(bool $clear = true): array
    {
        $messages = $this->core->session()->get(static::SESSION_FLASH_KEY, []);

        if ($clear) {
            $this->core->session()->remove(static::SESSION_FLASH_KEY);
        }

        return $messages;
    }

    /**
     * Adds a new notification for the current user.
     *
     * @codeCoverageIgnore until implemented
     *
     * @param string      $message
     * @param null|string $level
     * @return $this
     */
    public function addNotification(string $message, ?string $level = null): NotifierInterface
    {
        return $this;
    }

    /**
     * Marks all unread notifications for the current user read.
     *
     * @codeCoverageIgnore until implemented
     *
     * @return $this
     */
    public function markNotificationsRead(): NotifierInterface
    {
        return $this;
    }

    /**
     * Returns all unread notifications for the current user;
     *
     * @codeCoverageIgnore until implemented
     *
     * @return array
     */
    public function getUnreadNotifications(): array
    {
        return [];
    }

    /**
     * Returns all notifications set for the current user.
     *
     * @codeCoverageIgnore until implemented
     *
     * @return array
     */
    public function getAllNotifications(): array
    {
        return [];
    }

    /**
     * Returns notifications per page for the current user.
     *
     * @codeCoverageIgnore until implemented
     *
     * @param int $page
     * @param int $pageSize
     * @return array
     */
    public function getPaginatedNotifications(int $page = 1, int $pageSize = 10): array
    {
        return [];
    }

    /**
     * Deletes all notifications for the current user.
     *
     * @codeCoverageIgnore until implemented
     *
     * @return $this
     */
    public function clearNotifications(): NotifierInterface
    {
        return $this;
    }

}
