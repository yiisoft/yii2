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
            'title' => \$this->string(10)->notNull()->unique()->defaultValue("test")->after("id"),
            'body' => \$this->text()->notNull()->defaultValue("test")->after("title"),
            'address' => \$this->text()->notNull()->defaultValue("test")->after("body"),
            'address2' => \$this->text()->notNull()->defaultValue('te:st')->after("address"),
            'address3' => \$this->text()->notNull()->defaultValue(':te:st:')->after("address2"),
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
