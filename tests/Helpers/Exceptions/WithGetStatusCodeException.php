<?php
namespace Czim\CmsCore\Test\Helpers\Exceptions;

class WithGetStatusCodeException extends \Exception
{

    public function getStatusCode(): int
    {
        return 418;
    }

}
