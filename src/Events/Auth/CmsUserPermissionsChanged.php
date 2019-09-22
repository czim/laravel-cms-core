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

    public function __construct(UserInterface $user)
    {
        $this->user = $user;
    }

}
