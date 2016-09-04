<?php
namespace Czim\CmsCore\Contracts\Support\Data;

interface DataClearInterface
{

    /**
     * Clears the attributes.
     *
     * Note that this does not reset defaults, but clears them.
     *
     * @return $this
     */
    public function clear();

}
