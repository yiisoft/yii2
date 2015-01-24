<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\elasticsearch;

use Yii;
use yii\console\controllers\BaseMigrateController;
use yii\console\Exception;
use yii\di\Instance;
use yii\helpers\ArrayHelper;
use yii\helpers\Console;

/**
 * Manages application migrations using Elasticsearch.
 *
 * This is an analog of [[\yii\console\controllers\MigrateController]] but for Elasticsearch.
 *
 * This command provides support for tracking the migration history, upgrading
 * or downloading with migrations, and creating new migration skeletons.
 *
 * The migration history is stored in a Elasticsearch index/type specified by [[index]] and [[type]].
 * This index will be automatically created the first time this command is executed, if it does not exist.
 *
 * In order to enable this command you should adjust the configuration of your console application:
 *
 * ~~~
 * return [
 *     // ...
 *     'controllerMap' => [
 *         'es-migrate' => 'yii\elasticsearch\MigrateController',
 *         'index' => 'myindex', // set this to the index you want to use to store migration state
 *         'type' => 'migrations',
 *     ],
 * ];
 * ~~~
 *
 * Below are some common usages of this command:
 *
 * ~~~
 * # creates a new migration named 'create_user_index'
 * yii es-migrate/create create_user_index
 *
 * # applies ALL new migrations
 * yii es-migrate
 *
 * # reverts the last applied migration
 * yii es-migrate/down
 * ~~~
 *
 * @author Carsten Brandt <mail@cebe.cc>
 * @since 2.0.3
 */
class MigrateController extends BaseMigrateController
{
    /**
     * @var Connection|array|string the DB connection object or the application component ID of the DB connection.
     * Starting from version 2.0.2, this can also be a configuration array for creating the object.
     */
    public $db = 'elasticsearch';
    /**
     * @var string the name of the index that is used to store migration state.
     * Defaults to `yii2`.
     * @see type
     */
    public $index = 'yii2';
    /**
     * @var string the name of the type that is used to store migration state.
     * Defaults to `migrations`.
     * @see index
     */
    public $type = 'migrations';
    /**
     * @inheritdoc
     */
    public $templateFile = '@yii/mongodb/views/migration.php'; //TODO


    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        return array_merge(
            parent::options($actionID),
            ['index', 'type', 'db'] // global for all actions
        );
    }

    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            if ($action->id !== 'create') {
                $this->db = Instance::ensure($this->db, Connection::className());
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Creates a new migration instance.
     * @param string $class the migration class name
     * @return \yii\elasticsearch\Migration the migration instance
     */
    protected function createMigration($class)
    {
        $file = $this->migrationPath . DIRECTORY_SEPARATOR . $class . '.php';
        require_once($file);

        return new $class(['db' => $this->db]);
    }

    /**
     * @inheritdoc
     */
    protected function getMigrationHistory($limit)
    {
        $this->ensureBaseMigrationHistory();

        $query = new Query;
        $rows = $query->source(['version', 'apply_time'])
            ->from($this->index, $this->type)
            ->orderBy(['version' => SORT_DESC])
            ->limit($limit === null ? 10000 : $limit)
            ->all($this->db);
        $history = ArrayHelper::map(
            $rows,
            function($row) { return $row['_source']['version']; },
            function($row) { return $row['_source']['apply_time']; }
        );
        unset($history[self::BASE_MIGRATION]);
        return $history;
    }

    /**
     * Ensures migration history contains at least base migration entry.
     */
    protected function ensureBaseMigrationHistory()
    {
        $command = $this->db->createCommand();
        if (!$command->typeExists($this->index, $this->type)) {
            $this->stdout("Creating migration history index and type \"{$this->index}/{$this->type}\"...");

            if (!$command->indexExists($this->index)) {
                $command->createIndex($this->index);
            }
            $command->setMapping($this->index, $this->type, [
                $this->migrationTable => [
                    "_id" => [
                        "index" => "not_analyzed",
                        "store" => "yes",
                        "path" => "version"
                    ],
                    "properties" => [
                        "version" => ["type" => "string", "index" => "not_analyzed"],
                        "apply_time" => ["type" => "integer"],
                    ],
                ]
            ]);
            $this->stdout("done.\n", Console::FG_GREEN);
        }

        $baseMigration = $command->get($this->index, $this->type, self::BASE_MIGRATION);
        if ($baseMigration['found'] === false) {
            $this->addMigrationHistory(self::BASE_MIGRATION);
        }
    }

    /**
     * @inheritdoc
     */
    protected function addMigrationHistory($version)
    {
        $this->db->createCommand()->insert($this->index, $this->type, [
            'version' => $version,
            'apply_time' => time(),
        ], $version, [
            'op_type' => 'create',
            'refresh' => 'true'
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function removeMigrationHistory($version)
    {
        $this->db->createCommand()->delete($this->index, $this->type, $version);
    }
}
