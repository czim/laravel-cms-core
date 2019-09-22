<?php
namespace Czim\CmsCore\Http\Controllers;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class Controller extends BaseController
{
    use DispatchesJobs;
    use ValidatesRequests;


    /**
     * @var CoreInterface
     */
    protected $core;


    public function __construct(CoreInterface $core)
    {
        $this->core = $core;
    }

}
