<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ArrayAccessTrait provides the implementation for `IteratorAggregate`, `ArrayAccess` and `Countable`.
 *
 * Note that ArrayAccessTrait requires the class using it contain a property named `data` which should be an array.
 * The data will be exposed by ArrayAccessTrait to support accessing the class object like an array.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
trait ArrayAccessTrait
{
	/**
	 * Returns an iterator for traversing the data.
	 * This method is required by the SPL interface `IteratorAggregate`.
	 * It will be implicitly called when you use `foreach` to traverse the collection.
	 * @return \ArrayIterator an iterator for traversing the cookies in the collection.
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->data);
	}

	/**
	 * Returns the number of data items.
	 * This method is required by Countable interface.
	 * @return integer number of data elements.
	 */
	public function count()
	{
		return count($this->data);
	}

	/**
	 * This method is required by the interface ArrayAccess.
	 * @param mixed $offset the offset to check on
	 * @return boolean
	 */
	public function offsetExists($offset)
	{
		return isset($this->data[$offset]);
	}

	/**
	 * This method is required by the interface ArrayAccess.
	 * @param integer $offset the offset to retrieve element.
	 * @return mixed the element at the offset, null if no element is found at the offset
	 */
	public function offsetGet($offset)
	{
		return isset($this->data[$offset]) ? $this->data[$offset] : null;
	}

	/**
	 * This method is required by the interface ArrayAccess.
	 * @param integer $offset the offset to set element
	 * @param mixed $item the element value
	 */
	public function offsetSet($offset, $item)
	{
		$this->data[$offset] = $item;
	}

	/**
	 * This method is required by the interface ArrayAccess.
	 * @param mixed $offset the offset to unset element
	 */
	public function offsetUnset($offset)
	{
		unset($this->data[$offset]);
	}
}
