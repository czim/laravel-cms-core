<?php
namespace Czim\CmsCore\Test\Helpers\Modules;

use Czim\CmsCore\Contracts\Modules\ModuleGeneratorInterface;
use Czim\CmsCore\Contracts\Modules\ModuleInterface;
use Illuminate\Support\Collection;

class SimpleTestModuleGenerator implements ModuleGeneratorInterface
{

    /**
     * Generates and returns module instances.
     *
     * @return Collection|ModuleInterface[]
     */
    public function modules()
    {
        return [
            new SimpleTestModule(),
            new SimpleAssociatedTestModule(),
        ];
    }

}
