<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

use yii\base\InvalidConfigException;
use yii\db\Schema;
use yii\db\Migration;
use yii\log\DbTarget;

/**
 * Initializes log table.
 *
 * The indexes declared are not required. They are mainly used to improve the performance
 * of some queries about message levels and categories. Depending on your actual needs, you may
 * want to create additional indexes (e.g. index on `log_time`).
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0.1
 */
class m141106_185632_log_init extends Migration
{
    /**
     * @var DbTarget[]
     */
    private $dbTargets = [];

    /**
     * @throws InvalidConfigException
     * @return DbTarget[]
     */
    protected function getDbTargets()
    {
        if ($this->dbTargets === []) {
            $log = Yii::$app->getLog();

            foreach ($log->targets as $target) {
                if ($target instanceof DbTarget) {
                    $this->dbTargets[] = $target;
                }
            }

            if ($this->dbTargets === []) {
                throw new InvalidConfigException('You should configure "log" component to use one or more database targets before executing this migration.');
            }
        }
        return $this->dbTargets;
    }

    public function up()
    {
        $targets = $this->getDbTargets();
        foreach ($targets as $target) {
            $this->db = $target->db;

            $tableOptions = null;
            if ($this->db->driverName === 'mysql') {
                // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
                $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
            }

            $this->createTable($target->logTable, [
                'id' => Schema::TYPE_BIGPK,
                'level' => Schema::TYPE_INTEGER,
                'category' => Schema::TYPE_STRING,
                'log_time' => Schema::TYPE_DOUBLE,
                'prefix' => Schema::TYPE_TEXT,
                'message' => Schema::TYPE_TEXT,
            ], $tableOptions);

            $this->createIndex('idx_log_level', $target->logTable, 'level');
            $this->createIndex('idx_log_category', $target->logTable, 'category');
        }
    }

    public function down()
    {
        $targets = $this->getDbTargets();
        foreach ($targets as $target) {
            $this->db = $target->db;

            $this->dropTable($target->logTable);
        }
    }
}
