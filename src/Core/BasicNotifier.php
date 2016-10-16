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
    const SESSION_FLASH_KEY = 'flash-messages';

    /**
     * The default message level
     */
    const DEFAULT_LEVEL = 'info';

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
    public function flash($message, $level = null)
    {
        if (null === $level) {
            $level = static::DEFAULT_LEVEL;
        }

        $messages = $this->core->session()->get(static::SESSION_FLASH_KEY, []);

        $messages[] = [
            'message' => $message,
            'level'   => $level,
        ];

        $this->core->session()->set(static::SESSION_FLASH_KEY, $messages);

        return $this;
    }

    /**
     * Returns any previously flashed messages.
     *
     * @param bool $clear whether to delete the flashed messages from the session
     * @return array set of associative arrays with message, level keys
     */
    public function getFlashed($clear = true)
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
     * @param string      $message
     * @param null|string $level
     * @return $this
     */
    public function addNotification($message, $level = null)
    {
        return $this;
    }

    /**
     * Marks all unread notifications for the current user read.
     *
     * @return $this
     */
    public function markNotificationsRead()
    {
        return $this;
    }

    /**
     * Returns all unread notifications for the current user;
     *
     * @return array
     */
    public function getUnreadNotifications()
    {
        return [];
    }

    /**
     * Returns all notifications set for the current user.
     *
     * @return array
     */
    public function getAllNotifications()
    {
        return [];
    }

    /**
     * Returns notifications per page for the current user.
     *
     * @param int $page
     * @param int $pageSize
     * @return array
     */
    public function getPaginatedNotifications($page = 1, $pageSize = 10)
    {
        return [];
    }

    /**
     * Deletes all notifications for the current user.
     *
     * @return $this
     */
    public function clearNotifications()
    {
        return $this;
    }

}
