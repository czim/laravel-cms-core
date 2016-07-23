<?php
namespace Czim\CmsCore\Events\Auth;

use Czim\CmsCore\Contracts\Auth\UserInterface;
use Czim\CmsCore\Events\AbstractCmsEvent;

/**
 * Class CmsUserLoggedOut
 *
 * A CMS user was deliberately logged out.
 */
class CmsUserLoggedOut extends AbstractCmsEvent
{

    /**
     * @var UserInterface
     */
    public $user;

    /**
     * @param UserInterface $user
     */
    public function __construct(UserInterface $user)
    {
        $this->user = $user;
    }

}
