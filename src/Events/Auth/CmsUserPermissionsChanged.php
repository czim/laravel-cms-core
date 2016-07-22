<?php
namespace Czim\CmsCore\Events\Auth;

use Czim\CmsCore\Contracts\Auth\UserInterface;
use Czim\CmsCore\Events\AbstractCmsEvent;

class CmsUserPermissionsChanged extends AbstractCmsEvent
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
