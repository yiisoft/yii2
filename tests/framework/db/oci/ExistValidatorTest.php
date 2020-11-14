<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\oci;

use yii\validators\ExistValidator;
use yiiunit\data\ar\Order;
use yiiunit\data\ar\OrderItem;

/**
 * @group db
 * @group oci
 * @group validators
 */
class ExistValidatorTest extends \yiiunit\framework\validators\ExistValidatorTest
{
    public $driverName = 'oci';

    /**
     * Test expresssion in targetAttribute.
     * @see https://github.com/yiisoft/yii2/issues/14304
     */
    public function testExpresionInAttributeColumnName()
    {
        $val = new ExistValidator([
           'targetClass' => OrderItem::className(),
           'targetAttribute' => ['id' => 'COALESCE([[order_id]], 0)'],
       ]);

        $m = new Order(['id' => 1]);
        $val->validateAttribute($m, 'id');
        $this->assertFalse($m->hasErrors('id'));
    }

}
