<?php
namespace Czim\CmsCore\Contracts\Modules;

use Illuminate\Support\Collection;

interface ModuleGeneratorInterface
{

    /**
     * Generates and returns module instances.
     *
     * @return Collection|ModuleInterface[]
     */
    public function modules(): Collection;

}
