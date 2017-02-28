<?php
namespace Czim\CmsCore\Test\Helpers;

class NonInstantiableClassWithDependency
{

    public function __construct(Does\Not\Exist $dependency)
    {
    }

}
