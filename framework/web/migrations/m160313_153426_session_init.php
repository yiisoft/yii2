<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
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
     * {@inheritdoc}
     */
    public function up()
    {
        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = sprintf('CHARACTER SET %s ENGINE=InnoDB', $this->db->effectiveCharset);
        }

        $this->createTable(
            '{{%session}}',
            [
                'id' => $this->string()->notNull(),
                'expire' => $this->integer(),
                'data' => $this->binary(),
                'PRIMARY KEY ([[id]])',
            ],
            $tableOptions,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropTable('{{%session}}');
    }
}
