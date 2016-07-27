<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\data\base;

/**
 * TraversableObject
 * Object that implements `\Traversable` and `\Countable`, but counting throws an exception;
 * Used for testing support for traversable objects instead of arrays.
 * @author Sam Mousa <sam@mousa.nl>
 * @since 2.0.8
 */
class TraversableObject implements \Iterator, \Countable
{
    private $data;
    private $position = 0;

    public function __construct(array $array)
    {
        $this->data = $array;
    }

    /**
     * @throws \Exception
     * @since 5.1.0
     */
    public function count()
    {
        throw new \Exception('Count called on object that should only be traversed.');
    }

    /**
     * @inheritdoc
     */
    public function current()
    {
        return $this->data[$this->position];
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * @inheritdoc
     */
    public function valid()
    {
        return array_key_exists($this->position, $this->data);
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        $this->position = 0;
    }
}
