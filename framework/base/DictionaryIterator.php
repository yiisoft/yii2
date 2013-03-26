<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * DictionaryIterator implements the SPL `Iterator` interface for [[Dictionary]].
 *
 * It allows [[Dictionary]] to return a new iterator for data traversing purpose.
 * You normally do not use this class directly.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DictionaryIterator implements \Iterator
{
	/**
	 * @var array the data to be iterated through
	 */
	private $_d;
	/**
	 * @var array list of keys in the map
	 */
	private $_keys;
	/**
	 * @var mixed current key
	 */
	private $_key;

	/**
	 * Constructor.
	 * @param array $data the data to be iterated through
	 */
	public function __construct(&$data)
	{
		$this->_d = &$data;
		$this->_keys = array_keys($data);
		$this->_key = reset($this->_keys);
	}

	/**
	 * Rewinds the index of the current item.
	 * This method is required by the SPL interface `Iterator`.
	 */
	public function rewind()
	{
		$this->_key = reset($this->_keys);
	}

	/**
	 * Returns the key of the current array element.
	 * This method is required by the SPL interface `Iterator`.
	 * @return mixed the key of the current array element
	 */
	public function key()
	{
		return $this->_key;
	}

	/**
	 * Returns the current array element.
	 * This method is required by the SPL interface `Iterator`.
	 * @return mixed the current array element
	 */
	public function current()
	{
		return $this->_d[$this->_key];
	}

	/**
	 * Moves the internal pointer to the next element.
	 * This method is required by the SPL interface `Iterator`.
	 */
	public function next()
	{
		$this->_key = next($this->_keys);
	}

	/**
	 * Returns whether there is an element at current position.
	 * This method is required by the SPL interface `Iterator`.
	 * @return boolean whether there is an item at current position.
	 */
	public function valid()
	{
		return $this->_key !== false;
	}
}
