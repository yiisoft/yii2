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
            'field_1' => \$this->dateTime()->notNull()->defaultValue('0000-00-00 00:00:00'),
            'field_2' => \$this->string()->defaultValue('default:value'),
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
