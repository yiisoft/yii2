<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\data\ar;

/**
 * Class DefaultPk
 *
 * @author Jan Waś <janek.jan@gmail.com>
 * @property integer $id
 */
class DefaultPk extends ActiveRecord
{
    public static function tableName()
    {
        return 'default_pk';
    }
}
