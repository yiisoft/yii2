<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db;

use Yii;

/**
 * LogStub is a mock ActiveRecord pointing to a logs database.
 */
class LogStub extends \yii\db\ActiveRecord
{
    public static function getDb()
    {
        return Yii::$app->get('db_logs');
    }

    public static function tableName()
    {
        return 'audit_log';
    }
}
