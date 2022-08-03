<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\web;

/**
 * SessionIterator implements an [[\Iterator|iterator]] for traversing session variables managed by [[Session]].
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class SessionIterator implements \Iterator
{
    /**
     * @var array list of keys in the map
     */
    private $_keys;
    /**
     * @var string|int|false current key
     */
    private $_key;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->_keys = array_keys(isset($_SESSION) ? $_SESSION : []);
        $this->rewind();
    }

    /**
     * Rewinds internal array pointer.
     * This method is required by the interface [[\Iterator]].
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        $this->_key = reset($this->_keys);
    }

    /**
     * Returns the key of the current array element.
     * This method is required by the interface [[\Iterator]].
     * @return string|int|null the key of the current array element
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->_key === false ? null : $this->_key;
    }

    /**
     * Returns the current array element.
     * This method is required by the interface [[\Iterator]].
     * @return mixed the current array element
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->_key !== false && isset($_SESSION[$this->_key]) ? $_SESSION[$this->_key] : null;
    }

    /**
     * Moves the internal pointer to the next array element.
     * This method is required by the interface [[\Iterator]].
     */
    #[\ReturnTypeWillChange]
    public function next()
    {
        do {
            $this->_key = next($this->_keys);
        } while ($this->_key !== false && !isset($_SESSION[$this->_key]));
    }

    /**
     * Returns whether there is an element at current position.
     * This method is required by the interface [[\Iterator]].
     * @return bool
     */
    #[\ReturnTypeWillChange]
    public function valid()
    {
        return $this->_key !== false;
    }
}
