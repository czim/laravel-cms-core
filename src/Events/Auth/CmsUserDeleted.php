<?php
namespace Czim\CmsCore\Events\Auth;

use Czim\CmsCore\Contracts\Auth\UserInterface;
use Czim\CmsCore\Events\AbstractCmsEvent;

class CmsUserDeleted extends AbstractCmsEvent
{

    /**
     * @var UserInterface
     */
    public $username;

    /**
     * @param string $username
     */
    public function __construct($username)
    {
        $this->username = $username;
    }

}
