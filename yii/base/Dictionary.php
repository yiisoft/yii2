<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use yii\helpers\ArrayHelper;

/**
 * Dictionary implements a collection that stores key-value pairs.
 *
 * You can access, add or remove an item with a key by using
 * [[itemAt()]], [[add()]], and [[remove()]].
 *
 * To get the number of the items in the dictionary, use [[getCount()]].
 *
 * Because Dictionary implements a set of SPL interfaces, it can be used
 * like a regular PHP array as follows,
 *
 * ~~~
 * $dictionary[$key] = $value;		   // add a key-value pair
 * unset($dictionary[$key]);			 // remove the value with the specified key
 * if (isset($dictionary[$key]))		 // if the dictionary contains the key
 * foreach ($dictionary as $key=>$value) // traverse the items in the dictionary
 * $n = count($dictionary);			  // returns the number of items in the dictionary
 * ~~~
 *
 * @property integer $count the number of items in the dictionary
 * @property array $keys The keys in the dictionary
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Dictionary extends Object implements \IteratorAggregate, \ArrayAccess, \Countable
{
	/**
	 * @var array internal data storage
	 */
	private $_d = array();

	/**
	 * Constructor.
	 * Initializes the dictionary with an array or an iterable object.
	 * @param mixed $data the initial data to be populated into the dictionary.
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
	 * Returns an iterator for traversing the items in the dictionary.
	 * This method is required by the SPL interface `IteratorAggregate`.
	 * It will be implicitly called when you use `foreach` to traverse the dictionary.
	 * @return DictionaryIterator an iterator for traversing the items in the dictionary.
	 */
	public function getIterator()
	{
		return new DictionaryIterator($this->_d);
	}

	/**
	 * Returns the number of items in the dictionary.
	 * This method is required by the SPL `Countable` interface.
	 * It will be implicitly called when you use `count($dictionary)`.
	 * @return integer number of items in the dictionary.
	 */
	public function count()
	{
		return $this->getCount();
	}

	/**
	 * Returns the number of items in the dictionary.
	 * @return integer the number of items in the dictionary
	 */
	public function getCount()
	{
		return count($this->_d);
	}

	/**
	 * Returns the keys stored in the dictionary.
	 * @return array the key list
	 */
	public function getKeys()
	{
		return array_keys($this->_d);
	}

	/**
	 * Returns the item with the specified key.
	 * @param mixed $key the key
	 * @return mixed the element with the specified key.
	 * Null if the key cannot be found in the dictionary.
	 */
	public function itemAt($key)
	{
		return isset($this->_d[$key]) ? $this->_d[$key] : null;
	}

	/**
	 * Adds an item into the dictionary.
	 * Note, if the specified key already exists, the old value will be overwritten.
	 * @param mixed $key key
	 * @param mixed $value value
	 * @throws Exception if the dictionary is read-only
	 */
	public function add($key, $value)
	{
		if ($key === null) {
			$this->_d[] = $value;
		} else {
			$this->_d[$key] = $value;
		}
	}

	/**
	 * Removes an item from the dictionary by its key.
	 * @param mixed $key the key of the item to be removed
	 * @return mixed the removed value, null if no such key exists.
	 * @throws Exception if the dictionary is read-only
	 */
	public function remove($key)
	{
		if (isset($this->_d[$key])) {
			$value = $this->_d[$key];
			unset($this->_d[$key]);
			return $value;
		} else { // the value is null
			unset($this->_d[$key]);
			return null;
		}
	}

	/**
	 * Removes all items from the dictionary.
	 * @param boolean $safeClear whether to clear every item by calling [[remove]].
	 * Defaults to false, meaning all items in the dictionary will be cleared directly
	 * without calling [[remove]].
	 */
	public function removeAll($safeClear = false)
	{
		if ($safeClear) {
			foreach (array_keys($this->_d) as $key) {
				$this->remove($key);
			}
		} else {
			$this->_d = array();
		}
	}

	/**
	 * Returns a value indicating whether the dictionary contains the specified key.
	 * @param mixed $key the key
	 * @return boolean whether the dictionary contains an item with the specified key
	 */
	public function has($key)
	{
		return isset($this->_d[$key]) || array_key_exists($key, $this->_d);
	}

	/**
	 * Returns the dictionary as a PHP array.
	 * @return array the list of items in array
	 */
	public function toArray()
	{
		return $this->_d;
	}

	/**
	 * Copies iterable data into the dictionary.
	 * Note, existing data in the dictionary will be cleared first.
	 * @param mixed $data the data to be copied from, must be an array or an object implementing `Traversable`
	 * @throws InvalidParamException if data is neither an array nor an iterator.
	 */
	public function copyFrom($data)
	{
		if (is_array($data) || $data instanceof \Traversable) {
			if (!empty($this->_d)) {
				$this->removeAll();
			}
			if ($data instanceof self) {
				$data = $data->_d;
			}
			foreach ($data as $key => $value) {
				$this->add($key, $value);
			}
		} else {
			throw new InvalidParamException('Data must be either an array or an object implementing Traversable.');
		}
	}

	/**
	 * Merges iterable data into the dictionary.
	 *
	 * Existing elements in the dictionary will be overwritten if their keys are the same as those in the source.
	 * If the merge is recursive, the following algorithm is performed:
	 *
	 * - the dictionary data is saved as $a, and the source data is saved as $b;
	 * - if $a and $b both have an array indexed at the same string key, the arrays will be merged using this algorithm;
	 * - any integer-indexed elements in $b will be appended to $a;
	 * - any string-indexed elements in $b will overwrite elements in $a with the same index;
	 *
	 * @param array|\Traversable $data the data to be merged with. It must be an array or object implementing Traversable
	 * @param boolean $recursive whether the merging should be recursive.
	 * @throws InvalidParamException if data is neither an array nor an object implementing `Traversable`.
	 */
	public function mergeWith($data, $recursive = true)
	{
		if (is_array($data) || $data instanceof \Traversable) {
			if ($data instanceof self) {
				$data = $data->_d;
			}
			if ($recursive) {
				if ($data instanceof \Traversable) {
					$d = array();
					foreach ($data as $key => $value) {
						$d[$key] = $value;
					}
					$this->_d = ArrayHelper::merge($this->_d, $d);
				} else {
					$this->_d = ArrayHelper::merge($this->_d, $data);
				}
			} else {
				foreach ($data as $key => $value) {
					$this->add($key, $value);
				}
			}
		} else {
			throw new InvalidParamException('The data to be merged with must be an array or an object implementing Traversable.');
		}
	}

	/**
	 * Returns whether there is an element at the specified offset.
	 * This method is required by the SPL interface `ArrayAccess`.
	 * It is implicitly called when you use something like `isset($dictionary[$offset])`.
	 * This is equivalent to [[contains]].
	 * @param mixed $offset the offset to check on
	 * @return boolean
	 */
	public function offsetExists($offset)
	{
		return $this->has($offset);
	}

	/**
	 * Returns the element at the specified offset.
	 * This method is required by the SPL interface `ArrayAccess`.
	 * It is implicitly called when you use something like `$value = $dictionary[$offset];`.
	 * This is equivalent to [[itemAt]].
	 * @param mixed $offset the offset to retrieve element.
	 * @return mixed the element at the offset, null if no element is found at the offset
	 */
	public function offsetGet($offset)
	{
		return $this->itemAt($offset);
	}

	/**
	 * Sets the element at the specified offset.
	 * This method is required by the SPL interface `ArrayAccess`.
	 * It is implicitly called when you use something like `$dictionary[$offset] = $item;`.
	 * If the offset is null, the new item will be appended to the dictionary.
	 * Otherwise, the existing item at the offset will be replaced with the new item.
	 * This is equivalent to [[add]].
	 * @param mixed $offset the offset to set element
	 * @param mixed $item the element value
	 */
	public function offsetSet($offset, $item)
	{
		$this->add($offset, $item);
	}

	/**
	 * Unsets the element at the specified offset.
	 * This method is required by the SPL interface `ArrayAccess`.
	 * It is implicitly called when you use something like `unset($dictionary[$offset])`.
	 * This is equivalent to [[remove]].
	 * @param mixed $offset the offset to unset element
	 */
	public function offsetUnset($offset)
	{
		$this->remove($offset);
	}
}
