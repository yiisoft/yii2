<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\ar;

/**
 * Class Storage
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 * @property int $id
 * @property array $data
 */
class Storage extends ActiveRecord
{
    public static function tableName()
    {
        return 'storage';
    }
}
