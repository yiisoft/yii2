<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use yii\base\Object;

/**
 * BatchQueryResult represents the query result from which you can retrieve the data in batches.
 *
 * BatchQueryResult is mainly used with [[Query::batch()]].
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class BatchQueryResult extends Object implements \Iterator
{
	/**
	 * @var Connection
	 */
	public $db;
	/**
	 * @var Query
	 */
	public $query;
	/**
	 * @var integer
	 */
	public $batchSize = 10;
	/**
	 * @var DataReader
	 */
	public $dataReader;

	private $_data;
	private $_index = -1;

	public function __destruct()
	{
		$this->reset();
	}

	public function reset()
	{
		if ($this->dataReader !== null) {
			$this->dataReader->close();
		}
		$this->dataReader = null;
		$this->_data = null;
		$this->_index = -1;
	}

	/**
	 * Resets the iterator to the initial state.
	 * This method is required by the interface Iterator.
	 */
	public function rewind()
	{
		$this->reset();
		$this->next();
	}

	/**
	 * Returns the index of the current row.
	 * This method is required by the interface Iterator.
	 * @return integer the index of the current row.
	 */
	public function key()
	{
		return $this->_index;
	}

	/**
	 * Returns the current row.
	 * This method is required by the interface Iterator.
	 * @return mixed the current row.
	 */
	public function current()
	{
		return $this->_data;
	}

	/**
	 * Moves the internal pointer to the next row.
	 * This method is required by the interface Iterator.
	 */
	public function next()
	{
		if ($this->dataReader === null) {
			$this->dataReader = $this->query->createCommand($this->db)->query();
			$this->_index = 0;
		} else {
			$this->_index++;
		}

		$rows = [];
		$count = 0;
		while ($count++ < $this->batchSize && ($row = $this->dataReader->read())) {
			$rows[] = $row;
		}
		if (empty($rows)) {
			$this->_data = null;
		} else {
			$this->_data = $this->query->prepareResult($rows);
			if ($this->batchSize == 1) {
				$this->_data = reset($this->_data);
			}
		}
	}

	/**
	 * Returns whether there is a row of data at current position.
	 * This method is required by the interface Iterator.
	 * @return boolean whether there is a row of data at current position.
	 */
	public function valid()
	{
		return $this->_data !== null;
	}
}
