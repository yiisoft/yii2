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
 * Has foreign keys to the tables:
 *
 * - `{{%user}}`
 * - `{{%product}}`
 * - `{{%user_order}}`
 */
class {$class} extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        \$this->addColumn('{{%{table}}}', 'user_id', \$this->integer());
        \$this->addColumn('{{%{table}}}', 'product_id', \$this->integer()->unsigned()->notNull());
        \$this->addColumn('{{%{table}}}', 'order_id', \$this->integer()->notNull());
        \$this->addColumn('{{%{table}}}', 'created_at', \$this->dateTime()->notNull());

        // creates index for column `user_id`
        \$this->createIndex(
            '{{%idx-{table}-user_id}}',
            '{{%{table}}}',
            'user_id'
        );

        // add foreign key for table `{{%user}}`
        \$this->addForeignKey(
            '{{%fk-{table}-user_id}}',
            '{{%{table}}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE'
        );

        // creates index for column `product_id`
        \$this->createIndex(
            '{{%idx-{table}-product_id}}',
            '{{%{table}}}',
            'product_id'
        );

        // add foreign key for table `{{%product}}`
        \$this->addForeignKey(
            '{{%fk-{table}-product_id}}',
            '{{%{table}}}',
            'product_id',
            '{{%product}}',
            'id',
            'CASCADE'
        );

        // creates index for column `order_id`
        \$this->createIndex(
            '{{%idx-{table}-order_id}}',
            '{{%{table}}}',
            'order_id'
        );

        // add foreign key for table `{{%user_order}}`
        \$this->addForeignKey(
            '{{%fk-{table}-order_id}}',
            '{{%{table}}}',
            'order_id',
            '{{%user_order}}',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `{{%user}}`
        \$this->dropForeignKey(
            '{{%fk-{table}-user_id}}',
            '{{%{table}}}'
        );

        // drops index for column `user_id`
        \$this->dropIndex(
            '{{%idx-{table}-user_id}}',
            '{{%{table}}}'
        );

        // drops foreign key for table `{{%product}}`
        \$this->dropForeignKey(
            '{{%fk-{table}-product_id}}',
            '{{%{table}}}'
        );

        // drops index for column `product_id`
        \$this->dropIndex(
            '{{%idx-{table}-product_id}}',
            '{{%{table}}}'
        );

        // drops foreign key for table `{{%user_order}}`
        \$this->dropForeignKey(
            '{{%fk-{table}-order_id}}',
            '{{%{table}}}'
        );

        // drops index for column `order_id`
        \$this->dropIndex(
            '{{%idx-{table}-order_id}}',
            '{{%{table}}}'
        );

        \$this->dropColumn('{{%{table}}}', 'user_id');
        \$this->dropColumn('{{%{table}}}', 'product_id');
        \$this->dropColumn('{{%{table}}}', 'order_id');
        \$this->dropColumn('{{%{table}}}', 'created_at');
    }
}

CODE;
