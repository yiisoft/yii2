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
 * Handles the creation of table `{{%test}}`.
 */
class {$class} extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        \$this->createTable('{{%test}}', [
            'id' => \$this->primaryKey(),
            'title' => \$this->string(10)->notNull()->unique()->defaultValue(",te,st"),
            'body' => \$this->text()->notNull()->defaultValue(",test"),
            'test' => \$this->custom(11,2,"s")->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        \$this->dropTable('{{%test}}');
    }
}

CODE;
