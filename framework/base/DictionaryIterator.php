<?php

namespace yii\base;

/**
 * CMapIterator class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CMapIterator implements an interator for {@link CMap}.
 *
 * It allows CMap to return a new iterator for traversing the items in the map.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CMapIterator.php 3186 2011-04-15 22:34:55Z alexander.makarow $
 * @package system.collections
 * @since 1.0
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
		$this->_d=&$data;
		$this->_keys=array_keys($data);
		$this->_key=reset($this->_keys);
	}

	/**
	 * Rewinds internal array pointer.
	 * This method is required by the interface Iterator.
	 */
	public function rewind()
	{
		$this->_key=reset($this->_keys);
	}

	/**
	 * Returns the key of the current array element.
	 * This method is required by the interface Iterator.
	 * @return mixed the key of the current array element
	 */
	public function key()
	{
		return $this->_key;
	}

	/**
	 * Returns the current array element.
	 * This method is required by the interface Iterator.
	 * @return mixed the current array element
	 */
	public function current()
	{
		return $this->_d[$this->_key];
	}

	/**
	 * Moves the internal pointer to the next array element.
	 * This method is required by the interface Iterator.
	 */
	public function next()
	{
		$this->_key=next($this->_keys);
	}

	/**
	 * Returns whether there is an element at current position.
	 * This method is required by the interface Iterator.
	 * @return boolean
	 */
	public function valid()
	{
		return $this->_key!==false;
	}
}
