<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

use yii\base\InvalidConfigException;
use yii\db\Migration;
use yii\log\DbTarget;

/**
 * 初始化日志表。
 *
 * 索引不是必需的。
 * 它们主要用于提高有关消息级别和类别的某些查询的性能。
 * 根据您的实际需要，您可能希望创建其他索引（例如 `log_time` 上的索引）。
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0.1
 */
class m141106_185632_log_init extends Migration
{
    /**
     * @var DbTarget[] 用于创建日志表的目标
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

            $usedTargets = [];
            foreach ($log->targets as $target) {
                if ($target instanceof DbTarget) {
                    $currentTarget = [
                        $target->db,
                        $target->logTable,
                    ];
                    if (!in_array($currentTarget, $usedTargets, true)) {
                        // do not create same table twice
                        $usedTargets[] = $currentTarget;
                        $this->dbTargets[] = $target;
                    }
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
        foreach ($this->getDbTargets() as $target) {
            $this->db = $target->db;

            $tableOptions = null;
            if ($this->db->driverName === 'mysql') {
                // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
                $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
            }

            $this->createTable($target->logTable, [
                'id' => $this->bigPrimaryKey(),
                'level' => $this->integer(),
                'category' => $this->string(),
                'log_time' => $this->double(),
                'prefix' => $this->text(),
                'message' => $this->text(),
            ], $tableOptions);

            $this->createIndex('idx_log_level', $target->logTable, 'level');
            $this->createIndex('idx_log_category', $target->logTable, 'category');
        }
    }

    public function down()
    {
        foreach ($this->getDbTargets() as $target) {
            $this->db = $target->db;

            $this->dropTable($target->logTable);
        }
    }
}
