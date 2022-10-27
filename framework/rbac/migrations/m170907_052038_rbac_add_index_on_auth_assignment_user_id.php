<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

use yii\base\InvalidConfigException;
use yii\db\Migration;
use yii\rbac\DbManager;

/**
 * Adds index on `user_id` column in `auth_assignment` table for performance reasons.
 *
 * @see https://github.com/yiisoft/yii2/pull/14765
 *
 * @author Ivan Buttinoni <ivan.buttinoni@cibi.it>
 * @since 2.0.13
 */
class m170907_052038_rbac_add_index_on_auth_assignment_user_id extends Migration
{
    public $column = 'user_id';
    public $index = 'auth_assignment_user_id_idx';

    /**
     * @throws yii\base\InvalidConfigException
     * @return DbManager
     */
    protected function getAuthManager()
    {
        $authManager = Yii::$app->getAuthManager();
        if (!$authManager instanceof DbManager) {
            throw new InvalidConfigException('You should configure "authManager" component to use database before executing this migration.');
        }

        return $authManager;
    }

    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $authManager = $this->getAuthManager();
        $this->db = $authManager->db;

        $this->createIndex($this->index, $authManager->assignmentTable, $this->column);
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $authManager = $this->getAuthManager();
        $this->db = $authManager->db;

        $this->dropIndex($this->index, $authManager->assignmentTable);
    }
}
