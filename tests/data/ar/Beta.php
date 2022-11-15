<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\ar;

/**
 * @property int $id
 * @property string $alpha_string_identifier
 * @property Alpha $alpha
 */
class Beta extends ActiveRecord
{
    public static function tableName()
    {
        return 'beta';
    }

    public function getAlpha()
    {
        return $this->hasOne(Alpha::className(), ['string_identifier' => 'alpha_string_identifier']);
    }
}
