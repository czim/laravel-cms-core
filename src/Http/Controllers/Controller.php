<?php
namespace Czim\CmsCore\Http\Controllers;

use Czim\CmsCore\Contracts\Core\CoreInterface;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;

abstract class Controller extends BaseController
{
    use DispatchesJobs, ValidatesRequests;


    /**
     * @var CoreInterface
     */
    protected $core;

    /**
     * @param CoreInterface    $core
     */
    public function __construct(CoreInterface $core)
    {
        $this->core = $core;
    }

}
