<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

return <<<CODE
<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{table}`.
 */
class {$class} extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        \$this->createTable('{table}', [
            'id' => \$this->primaryKey(),
            'address' => \$this->string(),
            'address2' => \$this->string(),
            'email' => \$this->string(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        \$this->dropTable('{table}');
    }
}

CODE;
