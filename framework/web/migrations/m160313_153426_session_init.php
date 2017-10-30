<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

use yii\db\Migration;

/**
 * Initializes Session tables.
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 2.0.8
 */
class m160313_153426_session_init extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $dataType = $this->binary();
        $tableOptions = null;

        switch ($this->db->driverName) {
            case 'mysql':
                // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
                $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
                break;
            case 'sqlsrv':
            case 'mssql':
            case 'dblib':
                $dataType = $this->text();
                break;
        }

        $this->createTable('{{%session}}', [
            'id' => $this->string()->notNull(),
            'expire' => $this->integer(),
            'data' => $dataType,
            'PRIMARY KEY ([[id]])',
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%session}}');
    }
}
