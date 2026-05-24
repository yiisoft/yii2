<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\ar;

/**
 * Class NullValues.
 *
 * @property int $id
 * @property int|null $var1
 * @property int|null $var2
 * @property int|null $var3
 * @property string|null $stringcol
 */
class NullValues extends ActiveRecord
{
    public static function tableName()
    {
        return 'null_values';
    }
}
