<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db;

/**
 * UserStub is a mock ActiveRecord for testing cross-database joins.
 */
class UserStub extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static $connection = 'db';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }
}
