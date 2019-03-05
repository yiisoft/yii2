<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\db;

use yii\base\BaseObject;

/**
 * BatchQueryResult 表示批量查询，你可以从中批量检索数据。
 *
 * 通常不直接实例化  BatchQueryResult。相反，
 * 你可以通过调用 [[Query::batch()]] 或 [[Query::each()]]。因为 BatchQueryResult 实现了 [[\Iterator]] 接口，
 * 所以可以对其进行迭代，以在每次迭代中获取一批数据。例如，
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
class BatchQueryResult extends BaseObject implements \Iterator
{
    /**
     * @var Connection 执行批处理查询时要使用的数据库连接。
     * 如果为 null，将使用 "db" 应用程序组件。
     */
    public $db;
    /**
     * @var Query 与批处理查询关联的查询对象。
     * 除非显式调用 [[reset()]] 之后，否则不要直接修改此属性。
     */
    public $query;
    /**
     * @var int 每批返回的行数。
     */
    public $batchSize = 100;
    /**
     * @var bool 是否在每次迭代期间返回一行。
     * 如果为 false，每次迭代将返回整批行。
     */
    public $each = false;

    /**
     * @var DataReader 与此批处理查询关联的数据读取器。
     */
    private $_dataReader;
    /**
     * @var array 当前批中检索到的数据
     */
    private $_batch;
    /**
     * @var mixed 当前迭代的值
     */
    private $_value;
    /**
     * @var string|int 当前迭代的键
     */
    private $_key;


    /**
     * Destructor.
     */
    public function __destruct()
    {
        // 确保光标已关闭
        $this->reset();
    }

    /**
     * 重置批处理查询。
     * 此方法将清除现有的批查询，以便执行新的批查询。
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
    }

    /**
     * 将迭代器重置为初始状态。
     * 此方法是接口 [[\Iterator]] 所必需的。
     */
    public function rewind()
    {
        $this->reset();
        $this->next();
    }

    /**
     * 将内部指针移动到下一个数据集。
     * 此方法是接口 [[\Iterator]] 所必需的。
     */
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
     * 获取下一批数据。
     * @return array 获取的数据
     */
    protected function fetchData()
    {
        if ($this->_dataReader === null) {
            $this->_dataReader = $this->query->createCommand($this->db)->query();
        }

        $rows = [];
        $count = 0;
        while ($count++ < $this->batchSize && ($row = $this->_dataReader->read())) {
            $rows[] = $row;
        }

        return $this->query->populate($rows);
    }

    /**
     * 返回当前数据集的索引。
     * 此方法是接口 [[\Iterator]] 所必需的。
     * @return int 当前行的索引。
     */
    public function key()
    {
        return $this->_key;
    }

    /**
     * 返回当前数据集。
     * 此方法是接口 [[\Iterator]] 所必需的。
     * @return mixed 当前数据集。
     */
    public function current()
    {
        return $this->_value;
    }

    /**
     * 返回当前位置是否存在有效的数据集。
     * 此方法是接口 [[\Iterator]] 所必需的。
     * @return bool 当前位置是否存在有效的数据集。
     */
    public function valid()
    {
        return !empty($this->_batch);
    }
}
