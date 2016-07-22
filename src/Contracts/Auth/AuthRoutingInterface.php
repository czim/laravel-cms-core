<?php
namespace Czim\CmsCore\Contracts\Auth;

interface AuthRoutingInterface
{

    /**
     * Returns router action for the CMS login form.
     *
     * @return string|array
     */
    public function getRouteLoginAction();

    /**
     * Returns router action for the CMS login, posting login credentials.
     *
     * @return string|array
     */
    public function getRouteLoginPostAction();

    /**
     * Returns router action for logging out of the CMS.
     *
     * @return string|array
     */
    public function getRouteLogoutAction();

    /**
     * Returns router action for password email request form.
     *
     * @return string|array
     */
    public function getRoutePasswordEmailGetAction();

    /**
     * Returns router action for posting request for password email.
     *
     * @return string|array
     */
    public function getRoutePasswordEmailPostAction();

    /**
     * Returns router action for posting request for the password reset form.
     *
     * @return string|array
     */
    public function getRoutePasswordResetGetAction();

    /**
     * Returns router action for posting request posting the password reset form.
     *
     * @return string|array
     */
    public function getRoutePasswordResetPostAction();

}
