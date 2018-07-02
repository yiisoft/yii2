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
 * Handles changing columns 'columns' of table `{table}`.
 */
class {$class} extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        \$this->alterColumn('{table}', 'title', \$this->string(10)->notNull());
        \$this->alterColumn('{table}', 'body', \$this->text()->notNull());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        \$this->alterColumn('{table}', 'title', \$this->int()->notNull());
        \$this->alterColumn('{table}', 'body', \$this->text()->null());
    }
}


CODE;
