<?php
namespace Czim\CmsCore\Test\Helpers\Support;

use Czim\CmsCore\Support\Data\AbstractDataObject;

/**
 * Class AnotherTestDataObject
 *
 * @property mixed $key
 * @property mixed $value
 */
class AnotherTestDataObject extends AbstractDataObject
{
    /**
     * Test merge method.
     *
     * To be called while performing merges on attribute of TestDataObject.
     *
     * @param AnotherTestDataObject $new
     */
    public function merge(AnotherTestDataObject $new)
    {
        $this->key   = $new->key   ?: $this->key;
        $this->value = $new->value ?: $this->value;
    }
}
