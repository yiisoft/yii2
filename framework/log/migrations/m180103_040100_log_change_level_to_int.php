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
 *
 * @author Dzhuneyt Ahmed <dzhuneyt@dzhuneyt.com>
 */
class m180103_040100_log_change_level_to_int extends Migration
{

    /**
     * @var DbTarget[] Targets to create log table for
     */
    protected $dbTargets = [];

    /**
     * @throws InvalidConfigException
     * @return DbTarget[]
     */
    protected function getDbTargets()
    {
        if ($this->dbTargets === []) {
            $logger = Yii::getLogger();
            if (!$logger instanceof \yii\log\Logger) {
                throw new InvalidConfigException('You should configure "logger" to be instance of "\yii\log\Logger" before executing this migration.');
            }

            $usedTargets = [];
            foreach ($logger->targets as $target) {
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
            $this->alterColumn($target->logTable, 'level', $this->integer());
        }
    }

    public function down()
    {
        foreach ($this->getDbTargets() as $target) {
            $this->db = $target->db;
            $this->alterColumn($target->logTable, 'level', $this->string());
        }
    }
}
