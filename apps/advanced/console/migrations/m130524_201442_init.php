<?php

use yii\db\Schema;

class m130524_201442_init extends \yii\db\Migration
{
	public function up()
	{
		$tableOptions = null;
		if ($this->db->driverName === 'mysql') {
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
		}

		$this->createTable('tbl_user', [
			'id' => Schema::TYPE_PK,
			'username' => Schema::TYPE_STRING . ' NOT NULL',
			'auth_key' => Schema::TYPE_STRING . '(32) NOT NULL',
			'password_hash' => Schema::TYPE_STRING . ' NOT NULL',
			'password_reset_token' => Schema::TYPE_STRING . '(32)',
			'email' => Schema::TYPE_STRING . ' NOT NULL',
			'role' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 10',

			'status' => Schema::TYPE_SMALLINT . ' NOT NULL DEFAULT 10',
			'create_time' => Schema::TYPE_INTEGER.' NOT NULL',
			'update_time' => Schema::TYPE_INTEGER.' NOT NULL',
		], $tableOptions);
	}

	public function down()
	{
		$this->dropTable('tbl_user');
	}
}
