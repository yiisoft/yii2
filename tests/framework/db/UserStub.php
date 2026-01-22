<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db;

use Yii;

/**
 * UserStub is a mock ActiveRecord for testing cross-database joins.
 */
class UserStub extends \yii\db\ActiveRecord
{
    public static function getDb()
    {
        return Yii::$app->get('db');
    }

    public static function tableName()
    {
        return 'user';
    }
}
