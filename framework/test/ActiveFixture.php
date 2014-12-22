<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\test;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\TableSchema;

/**
 * ActiveFixture represents a fixture backed up by a [[modelClass|ActiveRecord class]] or a [[tableName|database table]].
 *
 * Either [[modelClass]] or [[tableName]] must be set. You should also provide fixture data in the file
 * specified by [[dataFile]] or overriding [[getData()]] if you want to use code to generate the fixture data.
 *
 * When the fixture is being loaded, it will first call [[resetTable()]] to remove any existing data in the table.
 * It will then populate the table with the data returned by [[getData()]].
 *
 * After the fixture is loaded, you can access the loaded data via the [[data]] property. If you set [[modelClass]],
 * you will also be able to retrieve an instance of [[modelClass]] with the populated data via [[getModel()]].
 *
 * @property TableSchema $tableSchema The schema information of the database table associated with this
 * fixture. This property is read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActiveFixture extends BaseActiveFixture
{
    /**
     * @var string the name of the database table that this fixture is about. If this property is not set,
     * the table name will be determined via [[modelClass]].
     * @see modelClass
     */
    public $tableName;
    /**
     * @var string|boolean the file path or path alias of the data file that contains the fixture data
     * to be returned by [[getData()]]. If this is not set, it will default to `FixturePath/data/TableName.php`,
     * where `FixturePath` stands for the directory containing this fixture class, and `TableName` stands for the
     * name of the table associated with this fixture. You can set this property to be false to prevent loading any data.
     */
    public $dataFile;

    /**
     * @var TableSchema the table schema for the table associated with this fixture
     */
    private $_table;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (!isset($this->modelClass) && !isset($this->tableName)) {
            throw new InvalidConfigException('Either "modelClass" or "tableName" must be set.');
        }
    }

    /**
     * Loads the fixture.
     *
     * The default implementation will first clean up the table by calling [[resetTable()]].
     * It will then populate the table with the data returned by [[getData()]].
     *
     * If you override this method, you should consider calling the parent implementation
     * so that the data returned by [[getData()]] can be populated into the table.
     */
    public function load()
    {
        $this->resetTable();
        $this->data = [];
        $table = $this->getTableSchema();
        foreach ($this->getData() as $alias => $row) {
            $this->db->createCommand()->insert($table->fullName, $row)->execute();
            if ($table->sequenceName !== null) {
                foreach ($table->primaryKey as $pk) {
                    if (!isset($row[$pk])) {
                        $row[$pk] = $this->db->getLastInsertID($table->sequenceName);
                        break;
                    }
                }
            }
            $this->data[$alias] = $row;
        }
    }

    /**
     * Returns the fixture data.
     *
     * The default implementation will try to return the fixture data by including the external file specified by [[dataFile]].
     * The file should return an array of data rows (column name => column value), each corresponding to a row in the table.
     *
     * If the data file does not exist, an empty array will be returned.
     *
     * @return array the data rows to be inserted into the database table.
     */
    protected function getData()
    {
        if ($this->dataFile === null) {
            $class = new \ReflectionClass($this);
            $dataFile = dirname($class->getFileName()) . '/data/' . $this->getTableSchema()->fullName . '.php';

            return is_file($dataFile) ? require($dataFile) : [];
        } else {
            return parent::getData();
        }
    }

    /**
     * Removes all existing data from the specified table and resets sequence number to 1 (if any).
     * This method is called before populating fixture data into the table associated with this fixture.
     */
    protected function resetTable()
    {
        $table = $this->getTableSchema();
        $this->db->createCommand()->delete($table->fullName)->execute();
        if ($table->sequenceName !== null) {
            $this->db->createCommand()->resetSequence($table->fullName, 1)->execute();
        }
    }

    /**
     * @return TableSchema the schema information of the database table associated with this fixture.
     * @throws \yii\base\InvalidConfigException if the table does not exist
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
