<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * Vector implements an integer-indexed collection class.
 *
 * You can access, append, insert, remove an item from the vector
 * by calling methods such as [[itemAt()]], [[add()]], [[insertAt()]],
 * [[remove()]] and [[removeAt()]].
 *
 * To get the number of the items in the vector, use [[getCount()]].
 *
 * Because Vector implements a set of SPL interfaces, it can be used
 * like a regular PHP array as follows,
 *
 * ~~~
 * $vector[] = $item;				// append new item at the end
 * $vector[$index] = $item;			// set new item at $index
 * unset($vector[$index]);			// remove the item at $index
 * if (isset($vector[$index]))		// if the vector has an item at $index
 * foreach ($vector as $index => $item) // traverse each item in the vector
 * $n = count($vector);				// count the number of items
 * ~~~
 *
 * Note that if you plan to extend Vector by performing additional operations
 * with each addition or removal of an item (e.g. performing type check),
 * please make sure you override [[insertAt()]] and [[removeAt()]].
 *
 * @property integer $count the number of items in the vector
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Vector extends Object implements \IteratorAggregate, \ArrayAccess, \Countable
{
	/**
	 * @var array internal data storage
	 */
	private $_d = array();
	/**
	 * @var integer number of items
	 */
	private $_c = 0;

	/**
	 * Constructor.
	 * Initializes the vector with an array or an iterable object.
	 * @param mixed $data the initial data to be populated into the vector.
	 * This can be an array or an iterable object.
	 * @param array $config name-value pairs that will be used to initialize the object properties
	 * @throws Exception if data is not well formed (neither an array nor an iterable object)
	 */
	public function __construct($data = array(), $config = array())
	{
		if (!empty($data)) {
			$this->copyFrom($data);
		}
		parent::__construct($config);
	}

	/**
	 * Returns an iterator for traversing the items in the vector.
	 * This method is required by the SPL interface `IteratorAggregate`.
	 * It will be implicitly called when you use `foreach` to traverse the vector.
	 * @return VectorIterator an iterator for traversing the items in the vector.
	 */
	public function getIterator()
	{
		return new VectorIterator($this->_d);
	}

	/**
	 * Returns the number of items in the vector.
	 * This method is required by the SPL `Countable` interface.
	 * It will be implicitly called when you use `count($vector)`.
	 * @return integer number of items in the vector.
	 */
	public function count()
	{
		return $this->getCount();
	}

	/**
	 * Returns the number of items in the vector.
	 * @return integer the number of items in the vector
	 */
	public function getCount()
	{
		return $this->_c;
	}

	/**
	 * Returns the item at the specified index.
	 * @param integer $index the index of the item
	 * @return mixed the item at the index
	 * @throws InvalidParamException if the index is out of range
	 */
	public function itemAt($index)
	{
		if (isset($this->_d[$index])) {
			return $this->_d[$index];
		} elseif ($index >= 0 && $index < $this->_c) { // in case the value is null
			return $this->_d[$index];
		} else {
			throw new InvalidParamException('Index out of range: ' . $index);
		}
	}

	/**
	 * Appends an item at the end of the vector.
	 * @param mixed $item new item
	 * @return integer the zero-based index at which the item is added
	 * @throws Exception if the vector is read-only.
	 */
	public function add($item)
	{
		$this->insertAt($this->_c, $item);
		return $this->_c - 1;
	}

	/**
	 * Inserts an item at the specified position.
	 * Original item at the position and the following items will be moved
	 * one step towards the end.
	 * @param integer $index the specified position.
	 * @param mixed $item new item to be inserted into the vector
	 * @throws InvalidParamException if the index specified is out of range, or the vector is read-only.
	 */
	public function insertAt($index, $item)
	{
		if ($index === $this->_c) {
			$this->_d[$this->_c++] = $item;
		} elseif ($index >= 0 && $index < $this->_c) {
			array_splice($this->_d, $index, 0, array($item));
			$this->_c++;
		} else {
			throw new InvalidParamException('Index out of range: ' . $index);
		}
	}

	/**
	 * Removes an item from the vector.
	 * The vector will search for the item, and the first item found
	 * will be removed from the vector.
	 * @param mixed $item the item to be removed.
	 * @return mixed the index at which the item is being removed, or false
	 * if the item cannot be found in the vector.
	 * @throws Exception if the vector is read only.
	 */
	public function remove($item)
	{
		if (($index = $this->indexOf($item)) >= 0) {
			$this->removeAt($index);
			return $index;
		} else {
			return false;
		}
	}

	/**
	 * Removes an item at the specified position.
	 * @param integer $index the index of the item to be removed.
	 * @return mixed the removed item.
	 * @throws InvalidParamException if the index is out of range, or the vector is read only.
	 */
	public function removeAt($index)
	{
		if ($index >= 0 && $index < $this->_c) {
			$this->_c--;
			if ($index === $this->_c) {
				return array_pop($this->_d);
			} else {
				$item = $this->_d[$index];
				array_splice($this->_d, $index, 1);
				return $item;
			}
		} else {
			throw new InvalidParamException('Index out of range: ' . $index);
		}
	}

	/**
	 * Removes all items from the vector.
	 * @param boolean $safeClear whether to clear every item by calling [[removeAt]].
	 * Defaults to false, meaning all items in the vector will be cleared directly
	 * without calling [[removeAt]].
	 */
	public function removeAll($safeClear = false)
	{
		if ($safeClear) {
			for ($i = $this->_c - 1; $i >= 0; --$i) {
				$this->removeAt($i);
			}
		} else {
			$this->_d = array();
			$this->_c = 0;
		}
	}

	/**
	 * Returns a value indicating whether the vector contains the specified item.
	 * Note that the search is based on strict PHP comparison.
	 * @param mixed $item the item
	 * @return boolean whether the vector contains the item
	 */
	public function has($item)
	{
		return $this->indexOf($item) >= 0;
	}

	/**
	 * Returns the index of the specified item in the vector.
	 * The index is zero-based. If the item is not found in the vector, -1 will be returned.
	 * Note that the search is based on strict PHP comparison.
	 * @param mixed $item the item
	 * @return integer the index of the item in the vector (0 based), -1 if not found.
	 */
	public function indexOf($item)
	{
		$index = array_search($item, $this->_d, true);
		return $index === false ? -1 : $index;
	}

	/**
	 * Returns the vector as a PHP array.
	 * @return array the items in the vector.
	 */
	public function toArray()
	{
		return $this->_d;
	}

	/**
	 * Copies iterable data into the vector.
	 * Note, existing data in the vector will be cleared first.
	 * @param mixed $data the data to be copied from, must be an array or an object implementing `Traversable`
	 * @throws InvalidParamException if data is neither an array nor an object implementing `Traversable`.
	 */
	public function copyFrom($data)
	{
		if (is_array($data) || $data instanceof \Traversable) {
			if ($this->_c > 0) {
				$this->removeAll();
			}
			if ($data instanceof self) {
				$data = $data->_d;
			}
			foreach ($data as $item) {
				$this->add($item);
			}
		} else {
			throw new InvalidParamException('Data must be either an array or an object implementing Traversable.');
		}
	}

	/**
	 * Merges iterable data into the vector.
	 * New items will be appended to the end of the existing items.
	 * @param array|\Traversable $data the data to be merged with. It must be an array or object implementing Traversable
	 * @throws InvalidParamException if data is neither an array nor an object implementing `Traversable`.
	 */
	public function mergeWith($data)
	{
		if (is_array($data) || ($data instanceof \Traversable)) {
			if ($data instanceof Vector) {
				$data = $data->_d;
			}
			foreach ($data as $item) {
				$this->add($item);
			}
		} else {
			throw new InvalidParamException('The data to be merged with must be an array or an object implementing Traversable.');
		}
	}

	/**
	 * Returns a value indicating whether there is an item at the specified offset.
	 * This method is required by the SPL interface `ArrayAccess`.
	 * It is implicitly called when you use something like `isset($vector[$offset])`.
	 * @param integer $offset the offset to be checked
	 * @return boolean whether there is an item at the specified offset.
	 */
	public function offsetExists($offset)
	{
		return $offset >= 0 && $offset < $this->_c;
	}

	/**
	 * Returns the item at the specified offset.
	 * This method is required by the SPL interface `ArrayAccess`.
	 * It is implicitly called when you use something like `$value = $vector[$offset];`.
	 * This is equivalent to [[itemAt]].
	 * @param integer $offset the offset to retrieve item.
	 * @return mixed the item at the offset
	 * @throws Exception if the offset is out of range
	 */
	public function offsetGet($offset)
	{
		return $this->itemAt($offset);
	}

	/**
	 * Sets the item at the specified offset.
	 * This method is required by the SPL interface `ArrayAccess`.
	 * It is implicitly called when you use something like `$vector[$offset] = $item;`.
	 * If the offset is null or equal to the number of the existing items,
	 * the new item will be appended to the vector.
	 * Otherwise, the existing item at the offset will be replaced with the new item.
	 * @param integer $offset the offset to set item
	 * @param mixed $item the item value
	 * @throws Exception if the offset is out of range, or the vector is read only.
	 */
	public function offsetSet($offset, $item)
	{
		if ($offset === null || $offset === $this->_c) {
			$this->insertAt($this->_c, $item);
		} else {
			$this->removeAt($offset);
			$this->insertAt($offset, $item);
		}
	}

	/**
	 * Unsets the item at the specified offset.
	 * This method is required by the SPL interface `ArrayAccess`.
	 * It is implicitly called when you use something like `unset($vector[$offset])`.
	 * This is equivalent to [[removeAt]].
	 * @param integer $offset the offset to unset item
	 * @throws Exception if the offset is out of range, or the vector is read only.
	 */
	public function offsetUnset($offset)
	{
		$this->removeAt($offset);
	}
}
