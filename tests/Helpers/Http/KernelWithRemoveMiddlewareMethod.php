<?php

namespace Czim\CmsCore\Test\Helpers\Http;

use Illuminate\Foundation\Http\Kernel;

class KernelWithRemoveMiddlewareMethod extends Kernel
{

    /**
     * Removes all global middleware registered.
     */
    public function removeGlobalMiddleware()
    {
        $this->middleware = [];
    }

}
