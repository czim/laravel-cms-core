<?php
namespace Czim\CmsCore\Test\Helpers\Exceptions;

class WithStatusCodePropertyException extends \Exception
{

    /**
     * @var int
     */
    public $httpStatusCode = 418;

}
