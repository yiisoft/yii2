<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

use yii\base\InvalidConfigException;
use yii\web\DbSession;
use yii\db\Migration;

/**
 * Initializes Session tables
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 2.0.8
 */
class m160313_153426_session_init extends Migration
{

    /**
     * @throws yii\base\InvalidConfigException
     * @return DbSession
     */
    protected function getSession()
    {
        $session = Yii::$app->getSession();
        if (!$session instanceof DbSession) {
            throw new InvalidConfigException('You should configure "session" component to use database before executing this migration.');
        }
        return $session;
    }

    /**
     * @inheritdoc
     */
    public function up()
    {
        $session = $this->getSession();
        $this->db = $session->db;

        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $length = ini_get('session.hash_function') === 1 ? 256 : 40;
        $this->createTable($session->sessionTable, [
            'id' => $this->string($length)->notNull(),
            'expire' => $this->integer(),
            'data' => $this->binary(),
            'PRIMARY KEY ([[id]])',
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $session = $this->getSession();
        $this->db = $session->db;

        $this->dropTable($session->sessionTable);
    }
}
