<?php
namespace Czim\CmsCore\Test\Helpers\Support;

use Czim\CmsCore\Support\Data\AbstractDataObject;

/**
 * Class TestDataObject
 *
 * @property mixed $some
 * @property mixed $that
 * @property mixed $set
 * @property TestDataObject $test
 * @property AnotherTestDataObject $other
 * @property AnotherTestDataObject[] $others
 */
class TestDataObject extends AbstractDataObject
{
    protected $objects = [
        'test'   => self::class,
        'other'  => AnotherTestDataObject::class,
        'others' => AnotherTestDataObject::class . '[]',
    ];

    /**
     * Passthru for testing mergeAttribute method.
     *
     * @param string $attribute
     * @param mixed  $newValue
     */
    public function testMerge(string $attribute, $newValue): void
    {
        $this->mergeAttribute($attribute, $newValue);
    }
}
