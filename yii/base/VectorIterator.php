<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * VectorIterator implements the SPL `Iterator` interface for [[Vector]].
 *
 * It allows [[Vector]] to return a new iterator for data traversing purpose.
 * You normally do not use this class directly.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class VectorIterator implements \Iterator
{
	/**
	 * @var array the data to be iterated through
	 */
	private $_d;
	/**
	 * @var integer index of the current item
	 */
	private $_i;
	/**
	 * @var integer count of the data items
	 */
	private $_c;

	/**
	 * Constructor.
	 * @param array $data the data to be iterated through
	 */
	public function __construct(&$data)
	{
		$this->_d = &$data;
		$this->_i = 0;
		$this->_c = count($this->_d);
	}

	/**
	 * Rewinds the index of the current item.
	 * This method is required by the SPL interface `Iterator`.
	 */
	public function rewind()
	{
		$this->_i = 0;
	}

	/**
	 * Returns the key of the current item.
	 * This method is required by the SPL interface `Iterator`.
	 * @return integer the key of the current item
	 */
	public function key()
	{
		return $this->_i;
	}

	/**
	 * Returns the current item.
	 * This method is required by the SPL interface `Iterator`.
	 * @return mixed the current item
	 */
	public function current()
	{
		return $this->_d[$this->_i];
	}

	/**
	 * Moves the internal pointer to the next item.
	 * This method is required by the SPL interface `Iterator`.
	 */
	public function next()
	{
		$this->_i++;
	}

	/**
	 * Returns a value indicating whether there is an item at current position.
	 * This method is required by the SPL interface `Iterator`.
	 * @return boolean whether there is an item at current position.
	 */
	public function valid()
	{
		return $this->_i < $this->_c;
	}
}
