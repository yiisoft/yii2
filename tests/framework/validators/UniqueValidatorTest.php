<?php

namespace yiiunit\framework\validators;

use yii\validators\UniqueValidator;
use Yii;
use yiiunit\data\ar\ActiveRecord;
use yiiunit\data\ar\Customer;
use yiiunit\data\ar\Order;
use yiiunit\data\ar\OrderItem;
use yiiunit\data\ar\Profile;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\data\validators\models\ValidatorTestMainModel;
use yiiunit\data\validators\models\ValidatorTestRefModel;
use yiiunit\framework\db\DatabaseTestCase;

abstract class UniqueValidatorTest extends DatabaseTestCase
{
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

    public function testValidateInvalidAttribute()
    {
        $validator = new UniqueValidator();
        $messageError = Yii::t('yii', '{attribute} is invalid.', ['attribute' => 'Name']);

        /** @var Customer $customerModel */
        $customerModel = Customer::findOne(1);
        $customerModel->name = ['test array data'];
        $validator->validateAttribute($customerModel, 'name');
        $this->assertEquals($messageError, $customerModel->getFirstError('name'));

        $customerModel->clearErrors();

        $customerModel->name = 'test data';
        $customerModel->email = ['email@mail.com', 'email2@mail.com',];
        $validator->targetAttribute = ['email', 'name'];
        $validator->validateAttribute($customerModel, 'name');
        $this->assertEquals($messageError, $customerModel->getFirstError('name'));
    }

    public function testValidateAttributeDefault()
    {
        $val = new UniqueValidator();
        $m = ValidatorTestMainModel::find()->one();
        $val->validateAttribute($m, 'id');
        $this->assertFalse($m->hasErrors('id'));
        /** @var ValidatorTestRefModel $m */
        $m = ValidatorTestRefModel::findOne(1);
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
        /** @var ValidatorTestMainModel $m */
        $m = ValidatorTestMainModel::findOne(1);
        $val->validateAttribute($m, 'testMainVal');
        $this->assertFalse($m->hasErrors('testMainVal'));
        $m = ValidatorTestMainModel::findOne(1);
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
        /** @var OrderItem $m */
        $m = OrderItem::findOne(['order_id' => 1, 'item_id' => 2]);
        $val->validateAttribute($m, 'order_id');
        $this->assertFalse($m->hasErrors('order_id'));
        $m->item_id = 1;
        $val->validateAttribute($m, 'order_id');
        $this->assertTrue($m->hasErrors('order_id'));
        $this->assertStringStartsWith('The combination "1"-"1" of Order Id and Item Id', $m->getFirstError('order_id'));

        // validate new record
        $m = new OrderItem(['order_id' => 1, 'item_id' => 2]);
        $val->validateAttribute($m, 'order_id');
        $this->assertTrue($m->hasErrors('order_id'));
        $this->assertStringStartsWith('The combination "1"-"2" of Order Id and Item Id', $m->getFirstError('order_id'));
        $m = new OrderItem(['order_id' => 10, 'item_id' => 2]);
        $val->validateAttribute($m, 'order_id');
        $this->assertFalse($m->hasErrors('order_id'));

        $val = new UniqueValidator([
            'targetClass' => OrderItem::className(),
            'targetAttribute' => ['id' => 'order_id'],
        ]);
        // validate old record
        /** @var Order $m */
        $m = Order::findOne(1);
        $val->validateAttribute($m, 'id');
        $this->assertTrue($m->hasErrors('id'));
        $this->assertStringStartsWith('Id "1" has already been taken.', $m->getFirstError('id'));
        $m = Order::findOne(1);
        $m->id = 2;
        $val->validateAttribute($m, 'id');
        $this->assertTrue($m->hasErrors('id'));
        $this->assertStringStartsWith('Id "2" has already been taken.', $m->getFirstError('id'));
        $m = Order::findOne(1);
        $m->id = 10;
        $val->validateAttribute($m, 'id');
        $this->assertFalse($m->hasErrors('id'));

        $m = new Order(['id' => 1]);
        $val->validateAttribute($m, 'id');
        $this->assertTrue($m->hasErrors('id'));
        $this->assertStringStartsWith('Id "1" has already been taken.', $m->getFirstError('id'));
        $m = new Order(['id' => 10]);
        $val->validateAttribute($m, 'id');
        $this->assertFalse($m->hasErrors('id'));
    }

    public function testValidateTargetClass()
    {
        // Check whether "Description" and "address" aren't equal
        $val = new UniqueValidator([
            'targetClass' => Customer::className(),
            'targetAttribute' => ['description'=>'address'],
        ]);

        /** @var Profile $m */
        $m = Profile::findOne(1);
        $this->assertEquals('profile customer 1', $m->description);
        $val->validateAttribute($m, 'description');
        $this->assertFalse($m->hasErrors('description'));

        // ID of Profile is not equal to ID of Customer
        // (1, description = address2) <=> (2, address = address2)
        $m->description = 'address2';
        $val->validateAttribute($m, 'description');
        $this->assertTrue($m->hasErrors('description'));
        $m->clearErrors('description');

        // ID of Profile IS equal to ID of Customer
        // (1, description = address1) <=> (1, address = address1)
        // https://github.com/yiisoft/yii2/issues/10263
        $m->description = 'address1';
        $val->validateAttribute($m, 'description');
        $this->assertTrue($m->hasErrors('description'));
    }

    public function testValidateScopeNamespaceTargetClassForNewClass()
    {
        $validator = new UniqueValidator();

        /** @var Profile $profileModel */
        $profileModel = new Profile(['description'=>'profile customer 1']);
        $validator->validateAttribute($profileModel, 'description');
        $this->assertTrue($profileModel->hasErrors('description'));

        $profileModel->clearErrors();
        $validator->targetClass = 'yiiunit\data\ar\Profile';
        $validator->validateAttribute($profileModel, 'description');
        $this->assertTrue($profileModel->hasErrors('description'));

        $profileModel->clearErrors();
        $validator->targetClass = '\yiiunit\data\ar\Profile';
        $validator->validateAttribute($profileModel, 'description');
        $this->assertTrue($profileModel->hasErrors('description'));
    }

    public function testValidateScopeNamespaceTargetClass()
    {
        $validator = new UniqueValidator();

        /** @var Profile $profileModel */
        $profileModel = Profile::findOne(1);
        $validator->validateAttribute($profileModel, 'description');
        $this->assertFalse($profileModel->hasErrors('description'));

        $profileModel->clearErrors();
        $validator->targetClass = 'yiiunit\data\ar\Profile';
        $validator->validateAttribute($profileModel, 'description');
        $this->assertFalse($profileModel->hasErrors('description'));

        $profileModel->clearErrors();
        $validator->targetClass = '\yiiunit\data\ar\Profile';
        $validator->validateAttribute($profileModel, 'description');
        $this->assertFalse($profileModel->hasErrors('description'));
    }
}