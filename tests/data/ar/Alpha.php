<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\data\ar;

/**
 * @property int $id
 * @property string $string_identifier
 */
class Alpha extends ActiveRecord
{
    public static function tableName()
    {
        return 'alpha';
    }

    public function getBetas()
    {
        return $this->hasMany(Beta::className(), ['alpha_string_identifier' => 'string_identifier']);
    }
}