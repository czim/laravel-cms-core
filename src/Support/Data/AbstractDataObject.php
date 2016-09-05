<?php
namespace Czim\CmsCore\Support\Data;

use Czim\CmsCore\Contracts\Support\Data\DataClearInterface;
use Czim\DataObject\AbstractDataObject as CzimAbstractDataObject;
use Czim\DataObject\Contracts\DataObjectInterface;

abstract class AbstractDataObject extends CzimAbstractDataObject implements DataClearInterface
{

    /**
     * Attributes to convert to objects when accessed
     *
     * This is a way to make nested objects on the fly, without parsing the whole
     * tree beforehand. Simply add the attribute name and the data object class to
     * decorate it as:
     *
     *     'brand' => Brand::class,
     *
     * If the attribute is an array of arrays that should be decorated as data objects,
     * i.e. when it is a list of related items, you can set it to make individual
     * data objects for each item:
     *
     *     'brand' => Brand::class . '[]',
     *
     * @var array
     */
    protected $objects = [];


    /**
     * Converts attributes to specific dataobjects if configured to
     *
     * @param string $key
     * @return mixed|DataObjectInterface
     */
    public function &getAttributeValue($key)
    {
        if (    ! count($this->objects)
            ||  ! array_key_exists($key, $this->objects)
        ) {
            return parent::getAttributeValue($key);
        }

        if ( ! isset($this->attributes[$key])) {
            $null = null;
            return $null;
        }

        $dataObjectClass = $this->objects[$key];
        $dataObjectArray = false;

        if (substr($dataObjectClass, -2) === '[]') {
            $dataObjectClass = substr($dataObjectClass, 0, -2);
            $dataObjectArray = true;
        }

        if ($dataObjectArray) {

            if (is_array($this->attributes[$key])) {

                foreach ($this->attributes[$key] as &$item) {

                    if ( ! is_a($item, $dataObjectClass)) {
                        $item = new $dataObjectClass( (array) $item);
                    }
                }
            }

            unset($item);

        } else {

            if ( ! is_a($this->attributes[ $key ], $dataObjectClass)) {
                $this->attributes[ $key ] = new $dataObjectClass((array) $this->attributes[ $key ]);
            }
        }

        return $this->attributes[$key];
    }

    /**
     * Get the value for a given offset.
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        // let it behave like the magic getter, return null if it doesn't exist
        if ( ! $this->offsetExists($offset)) return null;

        return $this->getAttribute($offset);
    }

    /**
     * Merges a single attribute by key with a new given value.
     *
     * @param string $key
     * @param mixed  $mergeValue
     */
    protected function mergeAttribute($key, $mergeValue)
    {
        $current = $this[$key];

        if ($current instanceof DataObjectInterface && method_exists($current, 'merge')) {

            $class = get_class($current);

            if (is_array($mergeValue)) {
                $mergeValue = new $class($mergeValue);
            }

            // If we have nothing to merge with, don't bother
            if (null === $mergeValue) {
                return;
            }

            $mergeValue = $current->merge($mergeValue);
        }

        if (null === $mergeValue) {
            return;
        }

        $this[$key] = $mergeValue;
    }

    /**
     * Clears the attributes.
     *
     * Note that this does not reset defaults, but clears them.
     *
     * @return $this
     */
    public function clear()
    {
        foreach ($this->getKeys() as $key) {
            $this->attributes[ $key ] = null;
        }

        return $this;
    }

}
