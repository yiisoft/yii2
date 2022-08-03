<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\ar;

/**
 * Class Profile.
 *
 * @property int $id
 * @property string $description
 */
class Profile extends ActiveRecord
{
    public static function tableName()
    {
        return 'profile';
    }
}
