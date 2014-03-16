<?php

namespace yiiunit\framework\validators;

use yii\validators\UniqueValidator;
use Yii;
use yiiunit\data\ar\ActiveRecord;
use yiiunit\data\ar\Order;
use yiiunit\data\ar\OrderItem;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\data\validators\models\ValidatorTestMainModel;
use yiiunit\data\validators\models\ValidatorTestRefModel;
use yiiunit\framework\db\DatabaseTestCase;

class UniqueValidatorTest extends DatabaseTestCase
{
    protected $driverName = 'mysql';

    public function setUp()
    {
        parent::setUp();
        $this->mockApplication();
        ActiveRecord::$db = $this->getConnection();
    }

    public function testAssureMessageSetOnInit()
    {
        $val = new UniqueValidator();
        $this->assertTrue(is_string($val->message));
    }

    public function testValidateAttributeDefault()
    {
        $val = new UniqueValidator();
        $m = ValidatorTestMainModel::find()->one();
        $val->validateAttribute($m, 'id');
        $this->assertFalse($m->hasErrors('id'));
        $m = ValidatorTestRefModel::find(1);
        $val->validateAttribute($m, 'ref');
        $this->assertTrue($m->hasErrors('ref'));
        // new record:
        $m = new ValidatorTestRefModel();
        $m->ref = 5;
        $val->validateAttribute($m, 'ref');
        $this->assertTrue($m->hasErrors('ref'));
        $m = new ValidatorTestRefModel();
        $m->id = 7;
        $m->ref = 12121;
        $val->validateAttribute($m, 'ref');
        $this->assertFalse($m->hasErrors('ref'));
        $m->save(false);
        $val->validateAttribute($m, 'ref');
        $this->assertFalse($m->hasErrors('ref'));
        // array error
        $m = FakedValidationModel::createWithAttributes(['attr_arr' => ['a', 'b']]);
        $val->validateAttribute($m, 'attr_arr');
        $this->assertTrue($m->hasErrors('attr_arr'));
    }

    public function testValidateAttributeOfNonARModel()
    {
        $val = new UniqueValidator(['targetClass' => ValidatorTestRefModel::className(), 'targetAttribute' => 'ref']);
        $m = FakedValidationModel::createWithAttributes(['attr_1' => 5, 'attr_2' => 1313]);
        $val->validateAttribute($m, 'attr_1');
        $this->assertTrue($m->hasErrors('attr_1'));
        $val->validateAttribute($m, 'attr_2');
        $this->assertFalse($m->hasErrors('attr_2'));
    }

    public function testValidateNonDatabaseAttribute()
    {
        $val = new UniqueValidator(['targetClass' => ValidatorTestRefModel::className(), 'targetAttribute' => 'ref']);
        $m = ValidatorTestMainModel::find(1);
        $val->validateAttribute($m, 'testMainVal');
        $this->assertFalse($m->hasErrors('testMainVal'));
        $m = ValidatorTestMainModel::find(1);
        $m->testMainVal = 4;
        $val->validateAttribute($m, 'testMainVal');
        $this->assertTrue($m->hasErrors('testMainVal'));
    }

    public function testValidateAttributeAttributeNotInTableException()
    {
        $this->setExpectedException('yii\db\Exception');
        $val = new UniqueValidator();
        $m = new ValidatorTestMainModel();
        $val->validateAttribute($m, 'testMainVal');
    }

    public function testValidateCompositeKeys()
    {
        $val = new UniqueValidator([
            'targetClass' => OrderItem::className(),
            'targetAttribute' => ['order_id', 'item_id'],
        ]);
        // validate old record
        $m = OrderItem::find(['order_id' => 1, 'item_id' => 2]);
        $val->validateAttribute($m, 'order_id');
        $this->assertFalse($m->hasErrors('order_id'));
        $m->item_id = 1;
        $val->validateAttribute($m, 'order_id');
        $this->assertTrue($m->hasErrors('order_id'));

        // validate new record
        $m = new OrderItem(['order_id' => 1, 'item_id' => 2]);
        $val->validateAttribute($m, 'order_id');
        $this->assertTrue($m->hasErrors('order_id'));
        $m = new OrderItem(['order_id' => 10, 'item_id' => 2]);
        $val->validateAttribute($m, 'order_id');
        $this->assertFalse($m->hasErrors('order_id'));

        $val = new UniqueValidator([
            'targetClass' => OrderItem::className(),
            'targetAttribute' => ['id' => 'order_id'],
        ]);
        // validate old record
        $m = Order::find(1);
        $val->validateAttribute($m, 'id');
        $this->assertTrue($m->hasErrors('id'));
        $m = Order::find(1);
        $m->id = 2;
        $val->validateAttribute($m, 'id');
        $this->assertTrue($m->hasErrors('id'));
        $m = Order::find(1);
        $m->id = 10;
        $val->validateAttribute($m, 'id');
        $this->assertFalse($m->hasErrors('id'));

        $m = new Order(['id' => 1]);
        $val->validateAttribute($m, 'id');
        $this->assertTrue($m->hasErrors('id'));
        $m = new Order(['id' => 10]);
        $val->validateAttribute($m, 'id');
        $this->assertFalse($m->hasErrors('id'));
    }
}
