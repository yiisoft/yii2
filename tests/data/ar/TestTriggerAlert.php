<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\data\ar;

/**
 * Class TestTriggerAlert.
 *
 * @property int $id
 * @property string $stringcol
 */
class TestTriggerAlert extends ActiveRecord
{
    public static function tableName()
    {
        return 'test_trigger_alert';
    }
}
