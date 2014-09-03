<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\mongodb\console\controllers;

use Yii;
use yii\console\controllers\BaseMigrateController;
use yii\console\Exception;
use yii\mongodb\Connection;
use yii\mongodb\Query;
use yii\helpers\ArrayHelper;

/**
 * Manages application MongoDB migrations.
 *
 * This is an analog of [[\yii\console\controllers\MigrateController]] for MongoDB.
 *
 * This command provides support for tracking the migration history, upgrading
 * or downloading with migrations, and creating new migration skeletons.
 *
 * The migration history is stored in a MongoDB collection named
 * as [[migrationCollection]]. This collection will be automatically created the first time
 * this command is executed, if it does not exist.
 *
 * In order to enable this command you should adjust the configuration of your console application:
 *
 * ~~~
 * return [
 *     // ...
 *     'controllerMap' => [
 *         'mongodb-migrate' => 'yii\mongodb\console\controllers\MigrateController'
 *     ],
 * ];
 * ~~~
 *
 * Below are some common usages of this command:
 *
 * ~~~
 * # creates a new migration named 'create_user_collection'
 * yii mongodb-migrate/create create_user_collection
 *
 * # applies ALL new migrations
 * yii mongodb-migrate
 *
 * # reverts the last applied migration
 * yii mongodb-migrate/down
 * ~~~
 *
 * @author Klimov Paul <klimov@zfort.com>
 * @since 2.0
 */
class MigrateController extends BaseMigrateController
{
    /**
     * @var string|array the name of the collection for keeping applied migration information.
     */
    public $migrationCollection = 'migration';
    /**
     * @inheritdoc
     */
    public $templateFile = '@yii/mongodb/views/migration.php';
    /**
     * @var Connection|string the DB connection object or the application
     * component ID of the DB connection.
     */
    public $db = 'mongodb';


    /**
     * @inheritdoc
     */
    public function options($actionID)
    {
        return array_merge(
            parent::options($actionID),
            ['migrationCollection', 'db'] // global for all actions
        );
    }

    /**
     * This method is invoked right before an action is to be executed (after all possible filters.)
     * It checks the existence of the [[migrationPath]].
     * @param \yii\base\Action $action the action to be executed.
     * @throws Exception if db component isn't configured
     * @return boolean whether the action should continue to be executed.
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            if ($action->id !== 'create') {
                if (is_string($this->db)) {
                    $this->db = Yii::$app->get($this->db);
                }
                if (!$this->db instanceof Connection) {
                    throw new Exception("The 'db' option must refer to the application component ID of a MongoDB connection.");
                }
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Creates a new migration instance.
     * @param string $class the migration class name
     * @return \yii\mongodb\Migration the migration instance
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
        $rows = $query->select(['version', 'apply_time'])
            ->from($this->migrationCollection)
            ->orderBy('version DESC')
            ->limit($limit)
            ->all($this->db);
        $history = ArrayHelper::map($rows, 'version', 'apply_time');
        unset($history[self::BASE_MIGRATION]);

        return $history;
    }

    private $baseMigrationEnsured = false;

    /**
     * Ensures migration history contains at least base migration entry.
     */
    protected function ensureBaseMigrationHistory()
    {
        if (!$this->baseMigrationEnsured) {
            $query = new Query;
            $row = $query->select(['version'])
                ->from($this->migrationCollection)
                ->andWhere(['version' => self::BASE_MIGRATION])
                ->limit(1)
                ->one($this->db);
            if (empty($row)) {
                $this->addMigrationHistory(self::BASE_MIGRATION);
            }
            $this->baseMigrationEnsured = true;
        }
    }

    /**
     * @inheritdoc
     */
    protected function addMigrationHistory($version)
    {
        $this->db->getCollection($this->migrationCollection)->insert([
            'version' => $version,
            'apply_time' => time(),
        ]);
    }

    /**
     * @inheritdoc
     */
    protected function removeMigrationHistory($version)
    {
        $this->db->getCollection($this->migrationCollection)->remove([
            'version' => $version,
        ]);
    }
}
