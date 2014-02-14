<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use yii\base\Object;

/**
 * BatchQueryResult represents a batch query from which you can retrieve data in batches.
 *
 * You usually do not instantiate BatchQueryResult directly. Instead, you obtain it by
 * calling [[Query::batch()]] or [[Query::each()]]. Because BatchQueryResult implements the `Iterator` interface,
 * you can iterate it to obtain a batch of data in each iteration. For example,
 *
 * ```php
 * $query = (new Query)->from('tbl_user');
 * foreach ($query->batch() as $i => $users) {
 *     // $users represents the rows in the $i-th batch
 * }
 * ```
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class BatchQueryResult extends Object implements \Iterator
{
	/**
	 * @var Connection the DB connection to be used when performing batch query.
	 * If null, the "db" application component will be used.
	 */
	public $db;
	/**
	 * @var Query the query object associated with this batch query.
	 * Do not modify this property directly unless after [[reset()]] is called explicitly.
	 */
	public $query;
	/**
	 * @var DataReader the data reader associated with this batch query.
	 * Do not modify this property directly unless after [[reset()]] is called explicitly.
	 */
	public $dataReader;
	/**
	 * @var integer the number of rows to be returned in each batch.
	 */
	public $batchSize = 100;

	private $_data;
	private $_key;
	private $_index = -1;

	/**
	 * Destructor.
	 */
	public function __destruct()
	{
		// make sure cursor is closed
		$this->reset();
	}

	/**
	 * Resets the batch query.
	 * This method will clean up the existing batch query so that a new batch query can be performed.
	 */
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
	 * Returns the index of the current dataset.
	 * This method is required by the interface Iterator.
	 * @return integer the index of the current row.
	 */
	public function key()
	{
		return $this->batchSize == 1 ? $this->_key : $this->_index;
	}

	/**
	 * Returns the current dataset.
	 * This method is required by the interface Iterator.
	 * @return mixed the current dataset.
	 */
	public function current()
	{
		return $this->_data;
	}

	/**
	 * Moves the internal pointer to the next dataset.
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
				$row = reset($this->_data);
				$this->_key = key($this->_data);
				$this->_data = $row;
			}
		}
	}

	/**
	 * Returns whether there is a valid dataset at the current position.
	 * This method is required by the interface Iterator.
	 * @return boolean whether there is a valid dataset at the current position.
	 */
	public function valid()
	{
		return $this->_data !== null;
	}
}
