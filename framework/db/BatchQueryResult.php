<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yii\db;

use yii\base\Component;

/**
 * BatchQueryResult represents a batch query from which you can retrieve data in batches.
 *
 * You usually do not instantiate BatchQueryResult directly. Instead, you obtain it by
 * calling [[Query::batch()]] or [[Query::each()]]. Because BatchQueryResult implements the [[\Iterator]] interface,
 * you can iterate it to obtain a batch of data in each iteration. For example,
 *
 * ```php
 * $query = (new Query)->from('user');
 * foreach ($query->batch() as $i => $users) {
 *     // $users represents the rows in the $i-th batch
 * }
 * foreach ($query->each() as $user) {
 * }
 * ```
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class BatchQueryResult extends Component implements \Iterator
{
    /**
     * @event Event an event that is triggered when the batch query is reset.
     * @see reset()
     * @since 2.0.41
     */
    const EVENT_RESET = 'reset';
    /**
     * @event Event an event that is triggered when the last batch has been fetched.
     * @since 2.0.41
     */
    const EVENT_FINISH = 'finish';

    /**
     * @var Connection|null the DB connection to be used when performing batch query.
     * If null, the "db" application component will be used.
     */
    public $db;
    /**
     * @var Query the query object associated with this batch query.
     * Do not modify this property directly unless after [[reset()]] is called explicitly.
     */
    public $query;
    /**
     * @var int the number of rows to be returned in each batch.
     */
    public $batchSize = 100;
    /**
     * @var bool whether to return a single row during each iteration.
     * If false, a whole batch of rows will be returned in each iteration.
     */
    public $each = false;

    /**
     * @var DataReader the data reader associated with this batch query.
     */
    private $_dataReader;
    /**
     * @var array the data retrieved in the current batch
     */
    private $_batch;
    /**
     * @var mixed the value for the current iteration
     */
    private $_value;
    /**
     * @var string|int the key for the current iteration
     */
    private $_key;
    /**
     * @var int MSSQL error code for exception that is thrown when last batch is size less than specified batch size
     * @see https://github.com/yiisoft/yii2/issues/10023
     */
    private $mssqlNoMoreRowsErrorCode = -13;


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
        if ($this->_dataReader !== null) {
            $this->_dataReader->close();
        }
        $this->_dataReader = null;
        $this->_batch = null;
        $this->_value = null;
        $this->_key = null;
        $this->trigger(self::EVENT_RESET);
    }

    /**
     * Resets the iterator to the initial state.
     * This method is required by the interface [[\Iterator]].
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        $this->reset();
        $this->next();
    }

    /**
     * Moves the internal pointer to the next dataset.
     * This method is required by the interface [[\Iterator]].
     */
    #[\ReturnTypeWillChange]
    public function next()
    {
        if ($this->_batch === null || !$this->each || $this->each && next($this->_batch) === false) {
            $this->_batch = $this->fetchData();
            reset($this->_batch);
        }

        if ($this->each) {
            $this->_value = current($this->_batch);
            if ($this->query->indexBy !== null) {
                $this->_key = key($this->_batch);
            } elseif (key($this->_batch) !== null) {
                $this->_key = $this->_key === null ? 0 : $this->_key + 1;
            } else {
                $this->_key = null;
            }
        } else {
            $this->_value = $this->_batch;
            $this->_key = $this->_key === null ? 0 : $this->_key + 1;
        }
    }

    /**
     * Fetches the next batch of data.
     * @return array the data fetched
     * @throws Exception
     */
    protected function fetchData()
    {
        if ($this->_dataReader === null) {
            $this->_dataReader = $this->query->createCommand($this->db)->query();
        }

        $rows = $this->getRows();

        return $this->query->populate($rows);
    }

    /**
     * Reads and collects rows for batch
     * @return array
     * @since 2.0.23
     */
    protected function getRows()
    {
        $rows = [];
        $count = 0;

        try {
            while ($count++ < $this->batchSize) {
                if ($row = $this->_dataReader->read()) {
                    $rows[] = $row;
                } else {
                    // we've reached the end
                    $this->trigger(self::EVENT_FINISH);
                    break;
                }
            }
        } catch (\PDOException $e) {
            $errorCode = isset($e->errorInfo[1]) ? $e->errorInfo[1] : null;
            if ($this->getDbDriverName() !== 'sqlsrv' || $errorCode !== $this->mssqlNoMoreRowsErrorCode) {
                throw $e;
            }
        }

        return $rows;
    }

    /**
     * Returns the index of the current dataset.
     * This method is required by the interface [[\Iterator]].
     * @return int the index of the current row.
     */
    #[\ReturnTypeWillChange]
    public function key()
    {
        return $this->_key;
    }

    /**
     * Returns the current dataset.
     * This method is required by the interface [[\Iterator]].
     * @return mixed the current dataset.
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->_value;
    }

    /**
     * Returns whether there is a valid dataset at the current position.
     * This method is required by the interface [[\Iterator]].
     * @return bool whether there is a valid dataset at the current position.
     */
    #[\ReturnTypeWillChange]
    public function valid()
    {
        return !empty($this->_batch);
    }

    /**
     * Gets db driver name from the db connection that is passed to the `batch()`, if it is not passed it uses
     * connection from the active record model
     * @return string|null
     */
    private function getDbDriverName()
    {
        if (isset($this->db->driverName)) {
            return $this->db->driverName;
        }

        if (!empty($this->_batch)) {
            $key = array_keys($this->_batch)[0];
            if (isset($this->_batch[$key]->db->driverName)) {
                return $this->_batch[$key]->db->driverName;
            }
        }

        return null;
    }

    /**
     * Unserialization is disabled to prevent remote code execution in case application
     * calls unserialize() on user input containing specially crafted string.
     * @see https://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2020-15148
     * @since 2.0.38
     */
    public function __wakeup()
    {
        throw new \BadMethodCallException('Cannot unserialize ' . __CLASS__);
    }
}
