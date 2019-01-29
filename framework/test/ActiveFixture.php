<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\test;

use yii\base\InvalidConfigException;
use yii\db\TableSchema;

/**
 * ActiveFixture 代表一个 [[modelClass|ActiveRecord class]] 模型或者一个 [[tableName|database table]] 数据表的夹具。
 *
 * 你需要且必须设置 [[modelClass]] 或 [[tableName]] 两属性之一（它们指向你需要mock的模型或数据表）。同时，你也需要通过设置 [[dataFile]] 属性指向一个文件
 * 用于提供夹具数据。如果你想使用代码生成夹具数据，你也可以重写 [[getData()]] 方法。
 *
 * 当夹具被加载的时候，它首先会调用 [[resetTable()]] 方法清理表中已经存在的数据。
 * 接着，它将会把 [[getData()]] 返回的数据填入表中。
 *
 * 在夹具被加载后，你可以通过 [[data]] 属性访问加载好的数据。如果你设置了 [[modelClass]] 属性，你可以通过 [[getModel()]] 方法获得 [[modelClass]]
 * 的一个实例。
 *
 * 有关 ActiveFixture 更多细节和使用信息，参阅 [guide article on fixtures](guide:test-fixtures)
 *
 * @property TableSchema $tableSchema 夹具关联的数据表元数据，这个属性是只读的.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveFixture extends BaseActiveFixture
{
    /**
     * @var string 这个夹具对应的数据库表名。如果没有设置此属性，数据库表名将由 [[modelClass]] 决定。
     * @see modelClass
     */
    public $tableName;
    /**
     * @var string|bool 包含有夹具数据的文件路径名称，或者 [path alias](guide:concept-aliases)，这些数据将作为 [[getData()]] 的返回值。
     * 如果这个属性没有被设置，它将默认为 `FixturePath/data/TableName.php`，`FixturePath` 代表夹具类所在的目录，`TableName` 代表夹具相关的数据库表。
     * 如果你不想加载任何数据，你可以将此属性设置为false。
     */
    public $dataFile;

    /**
     * @var TableSchema 夹具关联的数据表模式。
     */
    private $_table;


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if ($this->modelClass === null && $this->tableName === null) {
            throw new InvalidConfigException('Either "modelClass" or "tableName" must be set.');
        }
    }

    /**
     * 加载夹具。
     *
     * 它用 [[getData()]] 返回的数据填充数据表。
     *
     * 如果你重写了这个方法，你需要考虑调用父类，这样 [[getData()]] 返回的数据才能被填充进数据表中。
     */
    public function load()
    {
        $this->data = [];
        $table = $this->getTableSchema();
        foreach ($this->getData() as $alias => $row) {
            $primaryKeys = $this->db->schema->insert($table->fullName, $row);
            $this->data[$alias] = array_merge($row, $primaryKeys);
        }
    }

    /**
     * 返回夹具数据。
     *
     * 这个方法的默认实现是尝试返回 [[dataFile]] 指定的外部文件中包含的夹具数据。
     * 这个外部文件应该返回一个包含许多数据行（形如 列名 => 列值）的数组，数组的每一个元素代表表中的一行数据。
     *
     * 如果数据文件不存在，它将返回一个空数组。
     *
     * @return array 将要被插入数据库表中的数据行。
     */
    protected function getData()
    {
        if ($this->dataFile === null) {

            if ($this->dataDirectory !== null) {
                $dataFile = $this->getTableSchema()->fullName . '.php';
            } else {
                $class = new \ReflectionClass($this);
                $dataFile = dirname($class->getFileName()) . '/data/' . $this->getTableSchema()->fullName . '.php';
            }

            return $this->loadData($dataFile, false);
        }
        return parent::getData();
    }

    /**
     * {@inheritdoc}
     */
    public function unload()
    {
        $this->resetTable();
        parent::unload();
    }

    /**
     * 从指定表中删除所有现有数据，并将序列号重置为1(如果有)。
     * 这个方法在将夹具数据填充到与该夹具关联的表之前调用。
     */
    protected function resetTable()
    {
        $table = $this->getTableSchema();
        $this->db->createCommand()->delete($table->fullName)->execute();
        if ($table->sequenceName !== null) {
            $this->db->createCommand()->executeResetSequence($table->fullName, 1);
        }
    }

    /**
     * @return TableSchema 夹具关联的数据表模式。
     * @throws \yii\base\InvalidConfigException 如果数据表不存在的话。
     */
    public function getTableSchema()
    {
        if ($this->_table !== null) {
            return $this->_table;
        }

        $db = $this->db;
        $tableName = $this->tableName;
        if ($tableName === null) {
            /* @var $modelClass \yii\db\ActiveRecord */
            $modelClass = $this->modelClass;
            $tableName = $modelClass::tableName();
        }

        $this->_table = $db->getSchema()->getTableSchema($tableName);
        if ($this->_table === null) {
            throw new InvalidConfigException("Table does not exist: {$tableName}");
        }

        return $this->_table;
    }
}
