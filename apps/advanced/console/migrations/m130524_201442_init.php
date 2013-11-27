<?php

use yii\db\Schema;

class m130524_201442_init extends \yii\db\Migration
{
	public function up()
	{
		switch ($this->db->driverName) {
			case "mysql":
			// MySQL-specific table options. Adjust if you plan working with another DBMS
			$tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';

			$this->createTable('tbl_user', [
				'id' => Schema::TYPE_PK,
				'username' => Schema::TYPE_STRING.' NOT NULL',
				'auth_key' => Schema::TYPE_STRING.'(32) NOT NULL',
				'password_hash' => Schema::TYPE_STRING.' NOT NULL',
				'password_reset_token' => Schema::TYPE_STRING.'(32)',
				'email' => Schema::TYPE_STRING.' NOT NULL',
				'role' => 'tinyint NOT NULL DEFAULT 10',

				'status' => 'tinyint NOT NULL DEFAULT 10',
				'create_time' => Schema::TYPE_INTEGER.' NOT NULL',
				'update_time' => Schema::TYPE_INTEGER.' NOT NULL',
			], $tableOptions);
			break;
			pgsql:
			default:
			$tableOptions = ''; // Adjust if you have special requests.
			$this->createTable('tbl_user', [
				'id' => Schema::TYPE_PK,
				'username' => Schema::TYPE_STRING.' NOT NULL',
				'auth_key' => Schema::TYPE_STRING.'(32) NOT NULL',
				'password_hash' => Schema::TYPE_STRING.' NOT NULL',
				'password_reset_token' => Schema::TYPE_STRING.'(32)',
				'email' => Schema::TYPE_STRING.' NOT NULL',
								// tinyint is not SQL standard - use smallint instead
				'role' => 'smallint NOT NULL DEFAULT 10',
				'status' => 'smallint NOT NULL DEFAULT 10',
								// using unix time stored in ints is not really standard - use timestamp instead
				'create_time' => Schema::TYPE_TIMESTAMP.' NOT NULL',
				'update_time' => Schema::TYPE_TIMESTAMP.' NOT NULL',
			], $tableOptions);
		}
	}

	public function down()
	{
		$this->dropTable('tbl_user');
	}
}
