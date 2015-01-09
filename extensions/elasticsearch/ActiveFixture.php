<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\elasticsearch;

use Yii;
use yii\base\InvalidConfigException;
use yii\test\BaseActiveFixture;

/**
 * ActiveFixture represents a fixture for testing backed up by an [[modelClass|ActiveRecord class]] or an elastic search index.
 *
 * Either [[modelClass]] or [[index]] and [[type]] must be set. You should also provide fixture data in the file
 * specified by [[dataFile]] or overriding [[getData()]] if you want to use code to generate the fixture data.
 *
 * When the fixture is being loaded, it will first call [[resetIndex()]] to remove any existing data in the index for the [[type]].
 * It will then populate the index with the data returned by [[getData()]].
 *
 * After the fixture is loaded, you can access the loaded data via the [[data]] property. If you set [[modelClass]],
 * you will also be able to retrieve an instance of [[modelClass]] with the populated data via [[getModel()]].
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0.2
 */
class ActiveFixture extends BaseActiveFixture
{
    /**
     * @var Connection|string the DB connection object or the application component ID of the DB connection.
     * After the DbFixture object is created, if you want to change this property, you should only assign it
     * with a DB connection object.
     */
    public $db = 'elasticsearch';
    /**
     * @var string the name of the index that this fixture is about. If this property is not set,
     * the name will be determined via [[modelClass]].
     * @see modelClass
     */
    public $index;
    /**
     * @var string the name of the type that this fixture is about. If this property is not set,
     * the name will be determined via [[modelClass]].
     * @see modelClass
     */
    public $type;
    /**
     * @var string|boolean the file path or path alias of the data file that contains the fixture data
     * to be returned by [[getData()]]. If this is not set, it will default to `FixturePath/data/Index/Type.php`,
     * where `FixturePath` stands for the directory containing this fixture class, `Index` stands for the elasticsearch [[index]] name
     * and `Type` stands for the [[type]] associated with this fixture.
     * You can set this property to be false to prevent loading any data.
     */
    public $dataFile;


    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (!isset($this->modelClass) && (!isset($this->index) || !isset($this->type))) {
            throw new InvalidConfigException('Either "modelClass" or "index" and "type" must be set.');
        }
        /* @var $modelClass ActiveRecord */
        $modelClass = $this->modelClass;
        if ($this->index === null) {
            $this->index = $modelClass::index();
        }
        if ($this->type === null) {
            $this->type = $modelClass::type();
        }
    }

    /**
     * Loads the fixture.
     *
     * The default implementation will first clean up the index by calling [[resetIndex()]].
     * It will then populate the index with the data returned by [[getData()]].
     *
     * If you override this method, you should consider calling the parent implementation
     * so that the data returned by [[getData()]] can be populated into the index.
     */
    public function load()
    {
        $this->resetIndex();
        $this->data = [];

        $mapping = $this->db->createCommand()->getMapping($this->index, $this->type);
        if (isset($mapping[$this->index]['mappings'][$this->type]['_id']['path'])) {
            $idField = $mapping[$this->index]['mappings'][$this->type]['_id']['path'];
        } else {
            $idField = '_id';
        }

        foreach ($this->getData() as $alias => $row) {
            $options = [];
            $id = isset($row[$idField]) ? $row[$idField] : null;
            if ($idField === '_id') {
                unset($row[$idField]);
            }
            if (isset($row['_parent'])) {
                $options['parent'] = $row['_parent'];
                unset($row['_parent']);
            }

            try {
                $response = $this->db->createCommand()->insert($this->index, $this->type, $row, $id, $options);
            } catch(\yii\db\Exception $e) {
                throw new \yii\base\Exception("Failed to insert fixture data \"$alias\": " . $e->getMessage() . "\n" . print_r($e->errorInfo, true), $e->getCode(), $e);
            }
            if ($id === null) {
                $row[$idField] = $response['_id'];
            }
            $this->data[$alias] = $row;
        }
        // ensure all data is flushed and immediately available in the test
        $this->db->createCommand()->flushIndex($this->index);
    }

    /**
     * Returns the fixture data.
     *
     * The default implementation will try to return the fixture data by including the external file specified by [[dataFile]].
     * The file should return an array of data rows (column name => column value), each corresponding to a row in the index.
     *
     * If the data file does not exist, an empty array will be returned.
     *
     * @return array the data rows to be inserted into the database index.
     */
    protected function getData()
    {
        if ($this->dataFile === null) {
            $class = new \ReflectionClass($this);
            $dataFile = dirname($class->getFileName()) . "/data/{$this->index}/{$this->type}.php";
            return is_file($dataFile) ? require($dataFile) : [];
        } else {
            return parent::getData();
        }
    }

    /**
     * Removes all existing data from the specified index and type.
     * This method is called before populating fixture data into the index associated with this fixture.
     */
    protected function resetIndex()
    {
        $this->db->createCommand([
            'index' => $this->index,
            'type' => $this->type,
            'queryParts' => ['query' => ['match_all' => new \stdClass()]],
        ])->deleteByQuery();
    }
}
