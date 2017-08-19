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
 * Handles dropping columns from table `{table}`.
 */
class {$class} extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        \$this->dropColumn('{table}', 'title');
        \$this->dropColumn('{table}', 'body');
        \$this->dropColumn('{table}', 'price');
        \$this->dropColumn('{table}', 'created_at');
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        \$this->addColumn('{table}', 'title', \$this->string(10)->notNull());
        \$this->addColumn('{table}', 'body', \$this->text()->notNull());
        \$this->addColumn('{table}', 'price', \$this->money(11,2)->notNull());
        \$this->addColumn('{table}', 'created_at', \$this->dateTime());
    }
}

CODE;
