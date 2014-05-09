<?php

namespace yiiunit\framework\validators;

use Yii;
use yii\base\Exception;
use yii\validators\ExistValidator;
use yiiunit\data\ar\ActiveRecord;
use yiiunit\data\ar\Order;
use yiiunit\data\ar\OrderItem;
use yiiunit\data\validators\models\ValidatorTestMainModel;
use yiiunit\data\validators\models\ValidatorTestRefModel;
use yiiunit\framework\db\DatabaseTestCase;

/**
 * @group validators
 */
class ExistValidatorTest extends DatabaseTestCase
{
    protected $driverName = 'mysql';

    public function setUp()
    {
        parent::setUp();
        $this->mockApplication();
        ActiveRecord::$db = $this->getConnection();
    }

    public function testValidateValueExpectedException()
    {
        try {
            $val = new ExistValidator();
            $val->validate('ref');
            $this->fail('Exception should have been thrown at this time');
        } catch (Exception $e) {
            $this->assertInstanceOf('yii\base\InvalidConfigException', $e);
            $this->assertEquals('The "targetClass" property must be set.', $e->getMessage());
        }
        // combine to save the time creating a new db-fixture set (likely ~5 sec)
        try {
            $val = new ExistValidator(['targetClass' => ValidatorTestMainModel::className()]);
            $val->validate('ref');
            $this->fail('Exception should have been thrown at this time');
        } catch (Exception $e) {
            $this->assertInstanceOf('yii\base\InvalidConfigException', $e);
            $this->assertEquals('The "targetAttribute" property must be configured as a string.', $e->getMessage());
        }
    }

    public function testValidateValue()
    {
        $val = new ExistValidator(['targetClass' => ValidatorTestRefModel::className(), 'targetAttribute' => 'id']);
        $this->assertTrue($val->validate(2));
        $this->assertTrue($val->validate(5));
        $this->assertFalse($val->validate(99));
        $this->assertFalse($val->validate(['1']));
    }

    public function testValidateAttribute()
    {
        // existing value on different table
        $val = new ExistValidator(['targetClass' => ValidatorTestMainModel::className(), 'targetAttribute' => 'id']);
        $m = ValidatorTestRefModel::findOne(['id' => 1]);
        $val->validateAttribute($m, 'ref');
        $this->assertFalse($m->hasErrors());
        // non-existing value on different table
        $val = new ExistValidator(['targetClass' => ValidatorTestMainModel::className(), 'targetAttribute' => 'id']);
        $m = ValidatorTestRefModel::findOne(['id' => 6]);
        $val->validateAttribute($m, 'ref');
        $this->assertTrue($m->hasErrors('ref'));
        // existing value on same table
        $val = new ExistValidator(['targetAttribute' => 'ref']);
        $m = ValidatorTestRefModel::findOne(['id' => 2]);
        $val->validateAttribute($m, 'test_val');
        $this->assertFalse($m->hasErrors());
        // non-existing value on same table
        $val = new ExistValidator(['targetAttribute' => 'ref']);
        $m = ValidatorTestRefModel::findOne(['id' => 5]);
        $val->validateAttribute($m, 'test_val_fail');
        $this->assertTrue($m->hasErrors('test_val_fail'));
        // check for given value (true)
        $val = new ExistValidator();
        $m = ValidatorTestRefModel::findOne(['id' => 3]);
        $val->validateAttribute($m, 'ref');
        $this->assertFalse($m->hasErrors());
        // check for given defaults (false)
        $val = new ExistValidator();
        $m = ValidatorTestRefModel::findOne(['id' => 4]);
        $m->a_field = 'some new value';
        $val->validateAttribute($m, 'a_field');
        $this->assertTrue($m->hasErrors('a_field'));
        // check array
        $val = new ExistValidator(['targetAttribute' => 'ref']);
        $m = ValidatorTestRefModel::findOne(['id' => 2]);
        $m->test_val = [1, 2, 3, 4, 5, 6];
        $val->validateAttribute($m, 'test_val');
        $this->assertTrue($m->hasErrors('test_val'));
    }

    public function testValidateCompositeKeys()
    {
        $val = new ExistValidator([
            'targetClass' => OrderItem::className(),
            'targetAttribute' => ['order_id', 'item_id'],
        ]);
        // validate old record
        $m = OrderItem::findOne(['order_id' => 1, 'item_id' => 2]);
        $val->validateAttribute($m, 'order_id');
        $this->assertFalse($m->hasErrors('order_id'));

        // validate new record
        $m = new OrderItem(['order_id' => 1, 'item_id' => 2]);
        $val->validateAttribute($m, 'order_id');
        $this->assertFalse($m->hasErrors('order_id'));
        $m = new OrderItem(['order_id' => 10, 'item_id' => 2]);
        $val->validateAttribute($m, 'order_id');
        $this->assertTrue($m->hasErrors('order_id'));

        $val = new ExistValidator([
            'targetClass' => OrderItem::className(),
            'targetAttribute' => ['id' => 'order_id'],
        ]);
        // validate old record
        $m = Order::findOne(1);
        $val->validateAttribute($m, 'id');
        $this->assertFalse($m->hasErrors('id'));
        $m = Order::findOne(1);
        $m->id = 10;
        $val->validateAttribute($m, 'id');
        $this->assertTrue($m->hasErrors('id'));

        $m = new Order(['id' => 1]);
        $val->validateAttribute($m, 'id');
        $this->assertFalse($m->hasErrors('id'));
        $m = new Order(['id' => 10]);
        $val->validateAttribute($m, 'id');
        $this->assertTrue($m->hasErrors('id'));
    }
}
