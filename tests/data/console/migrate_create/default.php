<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

return <<<CODE
<?php

use yii\db\Migration;

class {$class} extends Migration
{
    public function safeUp()
    {

    }

    public function safeDown()
    {
        echo "{$class} cannot be reverted.\\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "{$class} cannot be reverted.\\n";

        return false;
    }
    */
}

CODE;
