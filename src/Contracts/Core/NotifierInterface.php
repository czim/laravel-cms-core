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
    public function flash(string $message, ?string $level = null): NotifierInterface;

    /**
     * Returns any previously flashed messages.
     *
     * @param bool $clear   whether to delete the flashed messages from the session
     * @return array set of associative arrays with message, level keys
     */
    public function getFlashed(bool $clear = true): array;


    /**
     * Adds a new notification for the current user.
     *
     * @param string      $message
     * @param null|string $level
     * @return $this
     */
    public function addNotification(string $message, ?string $level = null): NotifierInterface;

    /**
     * Marks all unread notifications for the current user read.
     *
     * @return $this
     */
    public function markNotificationsRead(): NotifierInterface;

    /**
     * Returns all unread notifications for the current user;
     *
     * @return array
     */
    public function getUnreadNotifications(): array;

    /**
     * Returns all notifications set for the current user.
     *
     * @return array
     */
    public function getAllNotifications(): array;

    /**
     * Returns notifications per page for the current user.
     *
     * @param int $page
     * @param int $pageSize
     * @return array
     */
    public function getPaginatedNotifications(int $page = 1, int $pageSize = 10): array;

    /**
     * Deletes all notifications for the current user.
     *
     * @return $this
     */
    public function clearNotifications(): NotifierInterface;

}
