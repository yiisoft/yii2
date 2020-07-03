<?php


namespace yiiunit\data\helpers;


class ArrayAccessObject implements \ArrayAccess
{
    private $attributes;
    public $name = 'bar1';

    public function __construct($attributes)
    {
        $this->attributes = $attributes;
    }

    public function offsetExists($offset)
    {
        return isset($this->attributes[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->attributes[$offset];
    }

    public function offsetSet($offset, $value)
    {
    }

    public function offsetUnset($offset)
    {
    }
}
