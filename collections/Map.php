<?php

namespace yii\collections;

use yii\base\Object;
use yii\base\ArrayAccessTrait;

class Map extends Object implements IteratorAggregate, ArrayAccess, Countable
{
	use ArrayAccessTrait;

	/**
	 * @var array internal data storage. This is used by [[ArrayAccessTrait]] to
	 * implement the IteratorAggregate, ArrayAccess, and Countable interfaces.
	 */
	protected $data;

	/**
	 * @var boolean whether this list is read-only
	 */
	private $_readOnly = false;

	/**
	 * Constructor.
	 * Initializes the list with an array or an iterable object.
	 * @param array $data the initial data. Default is null, meaning no initialization.
	 * @param boolean $readOnly whether the list is read-only
	 * @throws CException If data is not null and neither an array nor an iterator.
	 */
	public function __construct($data = null, $readOnly = false)
	{
		if ($data !== null) {
			$this->copyFrom($data);
		}
		$this->setReadOnly($readOnly);
	}

	/**
	 * @return boolean whether this map is read-only or not. Defaults to false.
	 */
	public function getReadOnly()
	{
		return $this->_readOnly;
	}

	/**
	 * @param boolean $value whether this list is read-only or not
	 */
	public function setReadOnly($value)
	{
		$this->_readOnly = $value;
	}
}
