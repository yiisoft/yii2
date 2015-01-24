<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\elasticsearch;

use yii\base\Component;
use yii\db\MigrationInterface;
use yii\di\Instance;
use yii\helpers\Json;

/**
 * Migration is the base class for representing an Elasticsearch migration.
 *
 * Each child class of Migration represents an individual migration which
 * is identified by the child class name.
 *
 * Within each migration, the [[up()]] method should be overridden to contain the logic
 * for "upgrading" the database; while the [[down()]] method for the "downgrading"
 * logic.
 *
 * Migration provides a set of convenient methods for manipulating Elasticsearch data and schema.
 * For example, the [[createIndex()]] method can be used to create an index.
 * Compared with the same methods in [[Command]], these methods will display extra
 * information showing the method parameters and execution time, which may be useful when
 * applying migrations.
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0.3
 */
abstract class Migration extends Component implements MigrationInterface
{
    /**
     * @var Connection|array|string the DB connection object or the application component ID of the DB connection.
     * This can also be a configuration array for creating the object.
     */
    public $db = 'elasticsearch';


    // TODO pass index and type of the command here
    // TODO use reference to the command for output

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
     * Creates a command for execution.
     * @param array $config the configuration for the Command class
     * @return Command the DB command
     */
    protected function createCommand($config = [])
    {
        return $this->db->createCommand($config);
    }

    /**
     * creates an index
     * @param string $index index name.
     * @param array $configuration index configuration.
     * @return mixed the request result.
     * @see http://www.elasticsearch.org/guide/en/elasticsearch/reference/current/indices-create-index.html
     */
    public function createIndex($index, $configuration = null)
    {
        echo "    > create index \"{$index}\" ...";
        $time = microtime(true);
        $this->createCommand()->createIndex($index, $configuration);
        echo " done (time: " . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    // TODO add more shortcut functions
}
