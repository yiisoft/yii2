<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use ArrayIterator;
use Yii;
use yii\base\BaseObject;

/**
 * HeaderCollection is used by [[Response]] to maintain the currently registered HTTP headers.
 *
 * @property int $count The number of headers in the collection. This property is read-only.
 * @property ArrayIterator $iterator An iterator for traversing the headers in the collection. This property
 * is read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HeaderCollection extends BaseObject implements \IteratorAggregate, \ArrayAccess, \Countable
{
    /**
     * @var array the headers in this collection (indexed by the header names)
     */
    private $_headers = [];


    /**
     * Returns an iterator for traversing the headers in the collection.
     * This method is required by the SPL interface [[\IteratorAggregate]].
     * It will be implicitly called when you use `foreach` to traverse the collection.
     * @return ArrayIterator an iterator for traversing the headers in the collection.
     */
    public function getIterator()
    {
        return new ArrayIterator($this->_headers);
    }

    /**
     * Returns the number of headers in the collection.
     * This method is required by the SPL `Countable` interface.
     * It will be implicitly called when you use `count($collection)`.
     * @return int the number of headers in the collection.
     */
    public function count()
    {
        return $this->getCount();
    }

    /**
     * Returns the number of headers in the collection.
     * @return int the number of headers in the collection.
     */
    public function getCount()
    {
        return count($this->_headers);
    }

    /**
     * Returns the named header(s).
     * @param string $name the name of the header to return
     * @param mixed $default the value to return in case the named header does not exist
     * @param bool $first whether to only return the first header of the specified name.
     * If false, all headers of the specified name will be returned.
     * @return string|array the named header(s). If `$first` is true, a string will be returned;
     * If `$first` is false, an array will be returned.
     */
    public function get($name, $default = null, $first = true)
    {
        $name = strtolower($name);
        if (isset($this->_headers[$name])) {
            return $first ? reset($this->_headers[$name]) : $this->_headers[$name];
        }

        return $default;
    }

    /**
     * Adds a new header.
     * If there is already a header with the same name, it will be replaced.
     * @param string $name the name of the header
     * @param string $value the value of the header
     * @return $this the collection object itself
     */
    public function set($name, $value = '')
    {
        $name = strtolower($name);
        $this->_headers[$name] = (array) $value;

        return $this;
    }

    /**
     * Adds a new header.
     * If there is already a header with the same name, the new one will
     * be appended to it instead of replacing it.
     * @param string $name the name of the header
     * @param string $value the value of the header
     * @return $this the collection object itself
     */
    public function add($name, $value)
    {
        $name = strtolower($name);
        $this->_headers[$name][] = $value;

        return $this;
    }

    /**
     * Sets a new header only if it does not exist yet.
     * If there is already a header with the same name, the new one will be ignored.
     * @param string $name the name of the header
     * @param string $value the value of the header
     * @return $this the collection object itself
     */
    public function setDefault($name, $value)
    {
        $name = strtolower($name);
        if (empty($this->_headers[$name])) {
            $this->_headers[$name][] = $value;
        }

        return $this;
    }

    /**
     * Returns a value indicating whether the named header exists.
     * @param string $name the name of the header
     * @return bool whether the named header exists
     */
    public function has($name)
    {
        $name = strtolower($name);

        return isset($this->_headers[$name]);
    }

    /**
     * Removes a header.
     * @param string $name the name of the header to be removed.
     * @return array the value of the removed header. Null is returned if the header does not exist.
     */
    public function remove($name)
    {
        $name = strtolower($name);
        if (isset($this->_headers[$name])) {
            $value = $this->_headers[$name];
            unset($this->_headers[$name]);
            return $value;
        }

        return null;
    }

    /**
     * Removes all headers.
     */
    public function removeAll()
    {
        $this->_headers = [];
    }

    /**
     * Returns the collection as a PHP array.
     * @return array the array representation of the collection.
     * The array keys are header names, and the array values are the corresponding header values.
     */
    public function toArray()
    {
        return $this->_headers;
    }

    /**
     * Populates the header collection from an array.
     * @param array $array the headers to populate from
     * @since 2.0.3
     */
    public function fromArray(array $array)
    {
        $this->_headers = $array;
    }

    /**
     * Returns whether there is a header with the specified name.
     * This method is required by the SPL interface [[\ArrayAccess]].
     * It is implicitly called when you use something like `isset($collection[$name])`.
     * @param string $name the header name
     * @return bool whether the named header exists
     */
    public function offsetExists($name)
    {
        return $this->has($name);
    }

    /**
     * Returns the header with the specified name.
     * This method is required by the SPL interface [[\ArrayAccess]].
     * It is implicitly called when you use something like `$header = $collection[$name];`.
     * This is equivalent to [[get()]].
     * @param string $name the header name
     * @return string the header value with the specified name, null if the named header does not exist.
     */
    public function offsetGet($name)
    {
        return $this->get($name);
    }

    /**
     * Adds the header to the collection.
     * This method is required by the SPL interface [[\ArrayAccess]].
     * It is implicitly called when you use something like `$collection[$name] = $header;`.
     * This is equivalent to [[add()]].
     * @param string $name the header name
     * @param string $value the header value to be added
     */
    public function offsetSet($name, $value)
    {
        $this->set($name, $value);
    }

    /**
     * Removes the named header.
     * This method is required by the SPL interface [[\ArrayAccess]].
     * It is implicitly called when you use something like `unset($collection[$name])`.
     * This is equivalent to [[remove()]].
     * @param string $name the header name
     */
    public function offsetUnset($name)
    {
        $this->remove($name);
    }
}
