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
class m180103_040100_log_change_level_to_int extends m141106_185632_log_init
{

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
