<?php
namespace Czim\CmsCore\Events\Auth;

use Czim\CmsCore\Contracts\Auth\UserInterface;
use Czim\CmsCore\Events\AbstractCmsEvent;

/**
 * Class CmsUserLoggedIn
 *
 * A CMS user logged in, or re-authenticated whether through normal, stateless or forced means.
 */
class CmsUserLoggedIn extends AbstractCmsEvent
{

    /**
     * @var UserInterface
     */
    public $user;

    /**
     * @var bool
     */
    public $stateless;

    /**
     * @var bool
     */
    public $forced;

    /**
     * @param UserInterface $user
     * @param bool          $stateless
     * @param bool          $forced
     */
    public function __construct(UserInterface $user, $stateless = false, $forced = false)
    {
        $this->user      = $user;
        $this->stateless = $stateless;
        $this->forced    = $forced;
    }

}
