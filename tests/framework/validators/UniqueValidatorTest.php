<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\validators;

use Yii;
use yii\validators\UniqueValidator;
use yiiunit\data\ar\ActiveRecord;
use yiiunit\data\ar\Customer;
use yiiunit\data\ar\Document;
use yiiunit\data\ar\Order;
use yiiunit\data\ar\OrderItem;
use yiiunit\data\ar\Profile;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\data\validators\models\ValidatorTestMainModel;
use yiiunit\data\validators\models\ValidatorTestRefModel;
use yiiunit\framework\db\DatabaseTestCase;

abstract class UniqueValidatorTest extends DatabaseTestCase
{
    protected function setUp()
    {
        parent::setUp();
        ActiveRecord::$db = $this->getConnection();

        // destroy application, Validator must work without Yii::$app
        $this->destroyApplication();
    }

    public function testAssureMessageSetOnInit()
    {
        $val = new UniqueValidator();
        $this->assertInternalType('string', $val->message);
    }

    public function testCustomMessage()
    {
        // single attribute
        $customError = 'Custom message for Id with value "1"';
        $validator = new UniqueValidator([
            'message' => 'Custom message for {attribute} with value "{value}"',
        ]);
        $model = new Order();
        $model->id = 1;
        $validator->validateAttribute($model, 'id');
        $this->assertTrue($model->hasErrors('id'));
        $this->assertEquals($customError, $model->getFirstError('id'));

        // multiple attributes
        $customError = 'Custom message for Order Id and Item Id with values "1"-"1"';
        $validator = new UniqueValidator([
            'targetAttribute' => ['order_id', 'item_id'],
            'message' => 'Custom message for {attributes} with values {values}',
        ]);
        $model = OrderItem::findOne(['order_id' => 1, 'item_id' => 2]);
        $model->item_id = 1;
        $validator->validateAttribute($model, 'order_id');
        $this->assertTrue($model->hasErrors('order_id'));
        $this->assertEquals($customError, $model->getFirstError('order_id'));

        // fallback for deprecated `comboNotUnique` - should be removed on 2.1.0
        $validator = new UniqueValidator([
            'targetAttribute' => ['order_id', 'item_id'],
            'comboNotUnique' => 'Custom message for {attributes} with values {values}',
        ]);
        $model->clearErrors();
        $validator->validateAttribute($model, 'order_id');
        $this->assertTrue($model->hasErrors('order_id'));
        $this->assertEquals($customError, $model->getFirstError('order_id'));
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
        $customerModel->email = ['email@mail.com', 'email2@mail.com'];
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
        $this->expectException('yii\db\Exception');
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
            'targetAttribute' => ['description' => 'address'],
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
        $profileModel = new Profile(['description' => 'profile customer 1']);
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

    public function testValidateEmptyAttributeInStringField()
    {
        ValidatorTestMainModel::deleteAll();

        $val = new UniqueValidator();

        $m = new ValidatorTestMainModel(['field1' => '']);
        $m->id = 1;
        $val->validateAttribute($m, 'field1');
        $this->assertFalse($m->hasErrors('field1'));
        $m->save(false);

        $m = new ValidatorTestMainModel(['field1' => '']);
        $m->id = 2;
        $val->validateAttribute($m, 'field1');
        $this->assertTrue($m->hasErrors('field1'));
    }

    public function testValidateEmptyAttributeInIntField()
    {
        ValidatorTestRefModel::deleteAll();

        $val = new UniqueValidator();

        $m = new ValidatorTestRefModel(['ref' => 0]);
        $m->id = 1;
        $val->validateAttribute($m, 'ref');
        $this->assertFalse($m->hasErrors('ref'));
        $m->save(false);

        $m = new ValidatorTestRefModel(['ref' => 0]);
        $m->id = 2;
        $val->validateAttribute($m, 'ref');
        $this->assertTrue($m->hasErrors('ref'));
    }

    public function testPrepareParams()
    {
        $model = new FakedValidationModel();
        $model->val_attr_a = 'test value a';
        $model->val_attr_b = 'test value b';
        $model->val_attr_c = 'test value c';
        $attribute = 'val_attr_a';

        $targetAttribute = 'val_attr_b';
        $result = $this->invokeMethod(new UniqueValidator(), 'prepareConditions', [$targetAttribute, $model, $attribute]);
        $expected = ['val_attr_b' => 'test value a'];
        $this->assertEquals($expected, $result);

        $targetAttribute = ['val_attr_b', 'val_attr_c'];
        $result = $this->invokeMethod(new UniqueValidator(), 'prepareConditions', [$targetAttribute, $model, $attribute]);
        $expected = ['val_attr_b' => 'test value b', 'val_attr_c' => 'test value c'];
        $this->assertEquals($expected, $result);

        $targetAttribute = ['val_attr_a' => 'val_attr_b'];
        $result = $this->invokeMethod(new UniqueValidator(), 'prepareConditions', [$targetAttribute, $model, $attribute]);
        $expected = ['val_attr_b' => 'test value a'];
        $this->assertEquals($expected, $result);

        $targetAttribute = ['val_attr_b', 'val_attr_a' => 'val_attr_c'];
        $result = $this->invokeMethod(new UniqueValidator(), 'prepareConditions', [$targetAttribute, $model, $attribute]);
        $expected = ['val_attr_b' => 'test value b', 'val_attr_c' => 'test value a'];
        $this->assertEquals($expected, $result);

        // Add table prefix for column name
        $model = Profile::findOne(1);
        $attribute = 'id';
        $targetAttribute = 'id';
        $result = $this->invokeMethod(new UniqueValidator(), 'prepareConditions', [$targetAttribute, $model, $attribute]);
        $expected = ['{{' . Profile::tableName() . '}}.[[' . $attribute . ']]' => $model->id];
        $this->assertEquals($expected, $result);
    }

    public function testGetTargetClassWithFilledTargetClassProperty()
    {
        $validator = new UniqueValidator(['targetClass' => Profile::className()]);
        $model = new FakedValidationModel();
        $actualTargetClass = $this->invokeMethod($validator, 'getTargetClass', [$model]);

        $this->assertEquals(Profile::className(), $actualTargetClass);
    }

    public function testGetTargetClassWithNotFilledTargetClassProperty()
    {
        $validator = new UniqueValidator();
        $model = new FakedValidationModel();
        $actualTargetClass = $this->invokeMethod($validator, 'getTargetClass', [$model]);

        $this->assertEquals(FakedValidationModel::className(), $actualTargetClass);
    }

    public function testPrepareQuery()
    {
        $schema = $this->getConnection()->schema;

        $model = new ValidatorTestMainModel();
        $query = $this->invokeMethod(new UniqueValidator(), 'prepareQuery', [$model, ['val_attr_b' => 'test value a']]);
        $expected = "SELECT * FROM {$schema->quoteTableName('validator_main')} WHERE {$schema->quoteColumnName('val_attr_b')}=:qp0";
        $this->assertEquals($expected, $query->createCommand()->getSql());

        $params = ['val_attr_b' => 'test value b', 'val_attr_c' => 'test value a'];
        $query = $this->invokeMethod(new UniqueValidator(), 'prepareQuery', [$model, $params]);
        $expected = "SELECT * FROM {$schema->quoteTableName('validator_main')} WHERE ({$schema->quoteColumnName('val_attr_b')}=:qp0) AND ({$schema->quoteColumnName('val_attr_c')}=:qp1)";
        $this->assertEquals($expected, $query->createCommand()->getSql());

        $params = ['val_attr_b' => 'test value b'];
        $query = $this->invokeMethod(new UniqueValidator(['filter' => 'val_attr_a > 0']), 'prepareQuery', [$model, $params]);
        $expected = "SELECT * FROM {$schema->quoteTableName('validator_main')} WHERE ({$schema->quoteColumnName('val_attr_b')}=:qp0) AND (val_attr_a > 0)";
        $this->assertEquals($expected, $query->createCommand()->getSql());

        $params = ['val_attr_b' => 'test value b'];
        $query = $this->invokeMethod(new UniqueValidator(['filter' => function ($query) {
            $query->orWhere('val_attr_a > 0');
        }]), 'prepareQuery', [$model, $params]);
        $expected = "SELECT * FROM {$schema->quoteTableName('validator_main')} WHERE ({$schema->quoteColumnName('val_attr_b')}=:qp0) OR (val_attr_a > 0)";
        $this->assertEquals($expected, $query->createCommand()->getSql());
    }

    /**
     * Test ambiguous column name in select clause
     * @see https://github.com/yiisoft/yii2/issues/14042
     */
    public function testAmbiguousColumnName()
    {
        $validator = new UniqueValidator([
            'filter' => function ($query) {
                $query->joinWith('items', false);
            },
        ]);
        $model = new Order();
        $model->id = 42;
        $model->customer_id = 1;
        $model->total = 800;
        $model->save(false);
        $validator->validateAttribute($model, 'id');
        $this->assertFalse($model->hasErrors());
    }

    /**
     * Test expresssion in targetAttribute
     * @see https://github.com/yiisoft/yii2/issues/14304
     */
    public function testExpresionInAttributeColumnName()
    {
        $validator = new UniqueValidator([
            'targetAttribute' => [
                'title' => 'LOWER(title)',
            ],
        ]);
        $model = new Document();
        $model->id = 42;
        $model->title = 'Test';
        $model->content = 'test';
        $model->version = 1;
        $model->save(false);
        $validator->validateAttribute($model, 'title');
        $this->assertFalse($model->hasErrors(), 'There were errors: ' . json_encode($model->getErrors()));
    }
}
