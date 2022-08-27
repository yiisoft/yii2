<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

return <<<CODE
<?php

{$namespace}use yii\db\Migration;

/**
 * Handles adding columns to table `{{%{table}}}`.
 */
class {$class} extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        \$this->addColumn('{{%{table}}}', 'title', \$this->string(10)->notNull());
        \$this->addColumn('{{%{table}}}', 'body', \$this->text()->notNull());
        \$this->addColumn('{{%{table}}}', 'price', \$this->money(11,2)->notNull());
        \$this->addColumn('{{%{table}}}', 'created_at', \$this->dateTime());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        \$this->dropColumn('{{%{table}}}', 'title');
        \$this->dropColumn('{{%{table}}}', 'body');
        \$this->dropColumn('{{%{table}}}', 'price');
        \$this->dropColumn('{{%{table}}}', 'created_at');
    }
}

CODE;
