<?php

namespace yii\base;

/**
 * This file contains classes implementing Map feature.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CMap implements a collection that takes key-value pairs.
 *
 * You can access, add or remove an item with a key by using
 * {@link itemAt}, {@link add}, and {@link remove}.
 * To get the number of the items in the map, use {@link getCount}.
 * CMap can also be used like a regular array as follows,
 * <pre>
 * $map[$key]=$value; // add a key-value pair
 * unset($map[$key]); // remove the value with the specified key
 * if(isset($map[$key])) // if the map contains the key
 * foreach($map as $key=>$value) // traverse the items in the map
 * $n=count($map);  // returns the number of items in the map
 * </pre>
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CMap.php 3153 2011-04-01 12:50:06Z qiang.xue $
 * @package system.collections
 * @since 1.0
 */
class Dictionary extends Component implements \IteratorAggregate, \ArrayAccess, \Countable
{
	/**
	 * @var array internal data storage
	 */
	private $_d=array();
	/**
	 * @var boolean whether this list is read-only
	 */
	private $_r=false;

	/**
	 * Constructor.
	 * Initializes the list with an array or an iterable object.
	 * @param array $data the intial data. Default is null, meaning no initialization.
	 * @param boolean $readOnly whether the list is read-only
	 * @throws CException If data is not null and neither an array nor an iterator.
	 */
	public function __construct($data=null,$readOnly=false)
	{
		if($data!==null)
			$this->copyFrom($data);
		$this->setReadOnly($readOnly);
	}

	/**
	 * @return boolean whether this map is read-only or not. Defaults to false.
	 */
	public function getReadOnly()
	{
		return $this->_r;
	}

	/**
	 * @param boolean $value whether this list is read-only or not
	 */
	protected function setReadOnly($value)
	{
		$this->_r=$value;
	}

	/**
	 * Returns an iterator for traversing the items in the list.
	 * This method is required by the interface IteratorAggregate.
	 * @return CMapIterator an iterator for traversing the items in the list.
	 */
	public function getIterator()
	{
		return new CMapIterator($this->_d);
	}

	/**
	 * Returns the number of items in the map.
	 * This method is required by Countable interface.
	 * @return integer number of items in the map.
	 */
	public function count()
	{
		return $this->getCount();
	}

	/**
	 * Returns the number of items in the map.
	 * @return integer the number of items in the map
	 */
	public function getCount()
	{
		return count($this->_d);
	}

	/**
	 * @return array the key list
	 */
	public function getKeys()
	{
		return array_keys($this->_d);
	}

	/**
	 * Returns the item with the specified key.
	 * This method is exactly the same as {@link offsetGet}.
	 * @param mixed $key the key
	 * @return mixed the element at the offset, null if no element is found at the offset
	 */
	public function itemAt($key)
	{
		if(isset($this->_d[$key]))
			return $this->_d[$key];
		else
			return null;
	}

	/**
	 * Adds an item into the map.
	 * Note, if the specified key already exists, the old value will be overwritten.
	 * @param mixed $key key
	 * @param mixed $value value
	 * @throws CException if the map is read-only
	 */
	public function add($key,$value)
	{
		if(!$this->_r)
		{
			if($key===null)
				$this->_d[]=$value;
			else
				$this->_d[$key]=$value;
		}
		else
			throw new CException(Yii::t('yii','The map is read only.'));
	}

	/**
	 * Removes an item from the map by its key.
	 * @param mixed $key the key of the item to be removed
	 * @return mixed the removed value, null if no such key exists.
	 * @throws CException if the map is read-only
	 */
	public function remove($key)
	{
		if(!$this->_r)
		{
			if(isset($this->_d[$key]))
			{
				$value=$this->_d[$key];
				unset($this->_d[$key]);
				return $value;
			}
			else
			{
				// it is possible the value is null, which is not detected by isset
				unset($this->_d[$key]);
				return null;
			}
		}
		else
			throw new CException(Yii::t('yii','The map is read only.'));
	}

	/**
	 * Removes all items in the map.
	 */
	public function clear()
	{
		foreach(array_keys($this->_d) as $key)
			$this->remove($key);
	}

	/**
	 * @param mixed $key the key
	 * @return boolean whether the map contains an item with the specified key
	 */
	public function contains($key)
	{
		return isset($this->_d[$key]) || array_key_exists($key,$this->_d);
	}

	/**
	 * @return array the list of items in array
	 */
	public function toArray()
	{
		return $this->_d;
	}

	/**
	 * Copies iterable data into the map.
	 * Note, existing data in the map will be cleared first.
	 * @param mixed $data the data to be copied from, must be an array or object implementing Traversable
	 * @throws CException If data is neither an array nor an iterator.
	 */
	public function copyFrom($data)
	{
		if(is_array($data) || $data instanceof Traversable)
		{
			if($this->getCount()>0)
				$this->clear();
			if($data instanceof CMap)
				$data=$data->_d;
			foreach($data as $key=>$value)
				$this->add($key,$value);
		}
		else if($data!==null)
			throw new CException(Yii::t('yii','Map data must be an array or an object implementing Traversable.'));
	}

	/**
	 * Merges iterable data into the map.
	 *
	 * Existing elements in the map will be overwritten if their keys are the same as those in the source.
	 * If the merge is recursive, the following algorithm is performed:
	 * <ul>
	 * <li>the map data is saved as $a, and the source data is saved as $b;</li>
	 * <li>if $a and $b both have an array indxed at the same string key, the arrays will be merged using this algorithm;</li>
	 * <li>any integer-indexed elements in $b will be appended to $a and reindxed accordingly;</li>
	 * <li>any string-indexed elements in $b will overwrite elements in $a with the same index;</li>
	 * </ul>
	 *
	 * @param mixed $data the data to be merged with, must be an array or object implementing Traversable
	 * @param boolean $recursive whether the merging should be recursive.
	 *
	 * @throws CException If data is neither an array nor an iterator.
	 */
	public function mergeWith($data,$recursive=true)
	{
		if(is_array($data) || $data instanceof Traversable)
		{
			if($data instanceof CMap)
				$data=$data->_d;
			if($recursive)
			{
				if($data instanceof Traversable)
				{
					$d=array();
					foreach($data as $key=>$value)
						$d[$key]=$value;
					$this->_d=self::mergeArray($this->_d,$d);
				}
				else
					$this->_d=self::mergeArray($this->_d,$data);
			}
			else
			{
				foreach($data as $key=>$value)
					$this->add($key,$value);
			}
		}
		else if($data!==null)
			throw new CException(Yii::t('yii','Map data must be an array or an object implementing Traversable.'));
	}

	/**
	 * Merges two arrays into one recursively.
	 * If each array has an element with the same string key value, the latter
	 * will overwrite the former (different from array_merge_recursive).
	 * Recursive merging will be conducted if both arrays have an element of array
	 * type and are having the same key.
	 * For integer-keyed elements, the elements from the latter array will
	 * be appended to the former array.
	 * @param array $a array to be merged to
	 * @param array $b array to be merged from
	 * @return array the merged array (the original arrays are not changed.)
	 * @see mergeWith
	 */
	public static function mergeArray($a,$b)
	{
		foreach($b as $k=>$v)
		{
			if(is_integer($k))
				isset($a[$k]) ? $a[]=$v : $a[$k]=$v;
			else if(is_array($v) && isset($a[$k]) && is_array($a[$k]))
				$a[$k]=self::mergeArray($a[$k],$v);
			else
				$a[$k]=$v;
		}
		return $a;
	}

	/**
	 * Returns whether there is an element at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param mixed $offset the offset to check on
	 * @return boolean
	 */
	public function offsetExists($offset)
	{
		return $this->contains($offset);
	}

	/**
	 * Returns the element at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param integer $offset the offset to retrieve element.
	 * @return mixed the element at the offset, null if no element is found at the offset
	 */
	public function offsetGet($offset)
	{
		return $this->itemAt($offset);
	}

	/**
	 * Sets the element at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param integer $offset the offset to set element
	 * @param mixed $item the element value
	 */
	public function offsetSet($offset,$item)
	{
		$this->add($offset,$item);
	}

	/**
	 * Unsets the element at the specified offset.
	 * This method is required by the interface ArrayAccess.
	 * @param mixed $offset the offset to unset element
	 */
	public function offsetUnset($offset)
	{
		$this->remove($offset);
	}
}
