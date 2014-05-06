<?php

use yii\db\Schema;

class m140506_102106_rbac_init extends \yii\db\Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%auth_rule}}', [
            'name' => Schema::TYPE_STRING . '(64) NOT NULL',
            'data' => Schema::TYPE_TEXT,
            'created_at' => Schema::TYPE_INTEGER,
            'updated_at' => Schema::TYPE_INTEGER,
        ], $tableOptions);
        $this->addPrimaryKey('pk-auth_rule', '{{%auth_rule}}', 'name');

        $this->createTable('{{%auth_item}}', [
            'name' => Schema::TYPE_STRING . '(64) NOT NULL',
            'type' => Schema::TYPE_INTEGER . ' NOT NULL',
            'description' => Schema::TYPE_TEXT,
            'rule_name' => Schema::TYPE_STRING . '(64)',
            'data' => Schema::TYPE_TEXT,
            'created_at' => Schema::TYPE_INTEGER,
            'updated_at' => Schema::TYPE_INTEGER,
        ], $tableOptions);
        $this->addPrimaryKey('pk-auth_item', '{{%auth_item}}', 'name');
        $this->addForeignKey('fk-auth_item-rule_name', '{{%auth_item}}', 'rule_name', '{{%auth_rule}}', 'name', 'SET NULL', 'CASCADE');
        $this->createIndex('idx-auth_item-type', '{{%auth_item}}', 'type');

        $this->createTable('{{%auth_item_child}}', [
            'parent' => Schema::TYPE_STRING . '(64) NOT NULL',
            'child' => Schema::TYPE_STRING . '(64) NOT NULL',
        ], $tableOptions);
        $this->addPrimaryKey('pk-auth_item_child', '{{%auth_item_child}}', ['parent', 'child']);
        $this->addForeignKey('fk-auth_item_child-parent', '{{%auth_item_child}}', 'parent', '{{%auth_item}}', 'name', 'CASCADE', 'CASCADE');
        $this->addForeignKey('fk-auth_item_child-child', '{{%auth_item_child}}', 'child', '{{%auth_item}}', 'name', 'CASCADE', 'CASCADE');

        $this->createTable('{{%auth_assignment}}', [
            'item_name' => Schema::TYPE_STRING . '(64) NOT NULL',
            'user_id' => Schema::TYPE_STRING . '(64) NOT NULL',
            'created_at' => Schema::TYPE_INTEGER,
        ], $tableOptions);
        $this->addPrimaryKey('pk-auth_assignment', '{{%auth_assignment}}', ['item_name', 'user_id']);
        $this->addForeignKey('fk-auth_assignment-item_name', '{{%auth_assignment}}', 'item_name', '{{%auth_item}}', 'name', 'CASCADE', 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('{{%auth_assignment}}');
        $this->dropTable('{{%auth_item_child}}');
        $this->dropTable('{{%auth_item}}');
        $this->dropTable('{{%auth_rule}}');
    }
}
