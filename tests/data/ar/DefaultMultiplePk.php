<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\ar;

/**
 * DefaultMultiplePk.
 *
 * @author mankwok <astleykwok@gmail.com>
 * @property int $id
 * @property string $second_key_column
 * @property string $type
 */
class DefaultMultiplePk extends ActiveRecord
{
    public static function tableName()
    {
        return 'default_multiple_pk';
    }
}
