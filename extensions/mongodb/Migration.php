<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mongodb;

use yii\base\Component;
use yii\db\MigrationInterface;
use yii\di\Instance;
use yii\helpers\Json;

/**
 * Migration is the base class for representing a MongoDB migration.
 *
 * Each child class of Migration represents an individual MongoDB migration which
 * is identified by the child class name.
 *
 * Within each migration, the [[up()]] method should be overridden to contain the logic
 * for "upgrading" the database; while the [[down()]] method for the "downgrading"
 * logic.
 *
 * Migration provides a set of convenient methods for manipulating MongoDB data and schema.
 * For example, the [[createIndex()]] method can be used to create a collection index.
 * Compared with the same methods in [[Collection]], these methods will display extra
 * information showing the method parameters and execution time, which may be useful when
 * applying migrations.
 *
 * @author Klimov Paul <klimov@zfort.com>
 * @since 2.0
 */
abstract class Migration extends Component implements MigrationInterface
{
    /**
     * @var Connection|string the MongoDB connection object or the application component ID of the MongoDB connection
     * that this migration should work with.
     */
    public $db = 'mongodb';

    /**
     * Initializes the migration.
     * This method will set [[db]] to be the 'db' application component, if it is null.
     */
    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::className());
    }

    /**
     * Creates new collection with the specified options.
     * @param string|array $collection name of the collection
     * @param array $options collection options in format: "name" => "value"
     * @return \MongoCollection new Mongo collection instance.
     */
    public function createCollection($collection, $options = [])
    {
        if (is_array($collection)) {
            list($database, $collectionName) = $collection;
        } else {
            $database = null;
            $collectionName = $collection;
        }
        echo "    > create collection " . $this->composeCollectionLogName($collection) . " ...";
        $time = microtime(true);
        $this->db->getDatabase($database)->createCollection($collectionName, $options);
        echo " done (time: " . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    /**
     * Drops existing collection.
     * @param string|array $collection name of the collection
     */
    public function dropCollection($collection)
    {
        echo "    > drop collection " . $this->composeCollectionLogName($collection) . " ...";
        $time = microtime(true);
        $this->db->getCollection($collection)->drop();
        echo " done (time: " . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    /**
     * Creates an index on the collection and the specified fields.
     * @param string|array $collection name of the collection
     * @param array|string $columns column name or list of column names.
     * @param array $options list of options in format: optionName => optionValue.
     */
    public function createIndex($collection, $columns, $options = [])
    {
        echo "    > create index on " . $this->composeCollectionLogName($collection) . " (" . Json::encode((array)$columns) . empty($options) ? "" : ", " . Json::encode($options) . ") ...";
        $time = microtime(true);
        $this->db->getCollection($collection)->createIndex($columns, $options);
        echo " done (time: " . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    /**
     * Drop indexes for specified column(s).
     * @param string|array $collection name of the collection
     * @param string|array $columns column name or list of column names.
     */
    public function dropIndex($collection, $columns)
    {
        echo "    > drop index on " . $this->composeCollectionLogName($collection) . " (" . Json::encode((array)$columns) . ") ...";
        $time = microtime(true);
        $this->db->getCollection($collection)->dropIndex($columns);
        echo " done (time: " . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    /**
     * Drops all indexes for specified collection.
     * @param string|array $collection name of the collection.
     */
    public function dropAllIndexes($collection)
    {
        echo "    > drop all indexes on " . $this->composeCollectionLogName($collection) . ") ...";
        $time = microtime(true);
        $this->db->getCollection($collection)->dropAllIndexes();
        echo " done (time: " . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    /**
     * Composes string representing collection name.
     * @param array|string $collection collection name.
     * @return string collection name.
     */
    protected function composeCollectionLogName($collection)
    {
        if (is_array($collection)) {
            list($database, $collection) = $collection;
            return $database . '.' . $collection;
        } else {
            return $collection;
        }
    }
}