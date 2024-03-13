<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\ar;

/**
 * DefaultPk.
 *
 * @author Jan Waś <janek.jan@gmail.com>
 * @property int $id
 */
class DefaultPk extends ActiveRecord
{
    public static function tableName()
    {
        return 'default_pk';
    }
}
