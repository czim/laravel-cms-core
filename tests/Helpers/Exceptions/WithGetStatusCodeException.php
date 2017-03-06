<?php
namespace Czim\CmsCore\Test\Helpers\Exceptions;

class WithGetStatusCodeException extends \Exception
{

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return 418;
    }

}
