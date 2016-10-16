<?php
namespace Czim\CmsCore\Contracts\Core;

interface NotifierInterface
{

    /**
     * Flashes a message to the session.
     *
     * @param string      $message
     * @param null|string $level
     * @return $this
     */
    public function flash($message, $level = null);

    /**
     * Returns any previously flashed messages.
     *
     * @param bool $clear   whether to delete the flashed messages from the session
     * @return array set of associative arrays with message, level keys
     */
    public function getFlashed($clear = true);


    /**
     * Adds a new notification for the current user.
     *
     * @param string      $message
     * @param null|string $level
     * @return $this
     */
    public function addNotification($message, $level = null);

    /**
     * Marks all unread notifications for the current user read.
     *
     * @return $this
     */
    public function markNotificationsRead();

    /**
     * Returns all unread notifications for the current user;
     *
     * @return array
     */
    public function getUnreadNotifications();

    /**
     * Returns all notifications set for the current user.
     *
     * @return array
     */
    public function getAllNotifications();

    /**
     * Returns notifications per page for the current user.
     *
     * @param int $page
     * @param int $pageSize
     * @return array
     */
    public function getPaginatedNotifications($page = 1, $pageSize = 10);

    /**
     * Deletes all notifications for the current user.
     *
     * @return $this
     */
    public function clearNotifications();

}
