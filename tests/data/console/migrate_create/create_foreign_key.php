<?php

return <<<CODE
<?php

use yii\db\Migration;

/**
 * Handles the creation of table `test`.
 * Has foreign keys to the tables:
 *
 * - `user`
 * - `product`
 * - `user_order`
 */
class {$class} extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        \$this->createTable('test', [
            'id' => \$this->primaryKey(),
            'user_id' => \$this->integer(),
            'product_id' => \$this->integer()->unsigned()->notNull(),
            'order_id' => \$this->integer()->notNull(),
            'created_at' => \$this->dateTime()->notNull(),
        ]);

        // creates index for column `user_id`
        \$this->createIndex(
            'idx-test-user_id',
            'test',
            'user_id'
        );

        // add foreign key for table `user`
        \$this->addForeignKey(
            'fk-test-user_id',
            'test',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );

        // creates index for column `product_id`
        \$this->createIndex(
            'idx-test-product_id',
            'test',
            'product_id'
        );

        // add foreign key for table `product`
        \$this->addForeignKey(
            'fk-test-product_id',
            'test',
            'product_id',
            'product',
            'id',
            'CASCADE'
        );

        // creates index for column `order_id`
        \$this->createIndex(
            'idx-test-order_id',
            'test',
            'order_id'
        );

        // add foreign key for table `user_order`
        \$this->addForeignKey(
            'fk-test-order_id',
            'test',
            'order_id',
            'user_order',
            'id',
            'CASCADE'
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        // drops foreign key for table `user`
        \$this->dropForeignKey(
            'fk-test-user_id',
            'test'
        );

        // drops index for column `user_id`
        \$this->dropIndex(
            'idx-test-user_id',
            'test'
        );

        // drops foreign key for table `product`
        \$this->dropForeignKey(
            'fk-test-product_id',
            'test'
        );

        // drops index for column `product_id`
        \$this->dropIndex(
            'idx-test-product_id',
            'test'
        );

        // drops foreign key for table `user_order`
        \$this->dropForeignKey(
            'fk-test-order_id',
            'test'
        );

        // drops index for column `order_id`
        \$this->dropIndex(
            'idx-test-order_id',
            'test'
        );

        \$this->dropTable('test');
    }
}

CODE;
