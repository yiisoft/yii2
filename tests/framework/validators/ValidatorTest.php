<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\validators;

use yii\base\DynamicModel;
use yii\validators\BooleanValidator;
use yii\validators\InlineValidator;
use yii\validators\NumberValidator;
use yii\validators\RequiredValidator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\data\validators\models\ValidatorTestFunctionModel;
use yiiunit\data\validators\TestValidator;
use yiiunit\TestCase;

/**
 * @group validators
 */
class ValidatorTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        // destroy application, Validator must work without Yii::$app
        $this->destroyApplication();
    }

    protected function getTestModel($additionalAttributes = [])
    {
        $attributes = array_merge(
            ['attr_runMe1' => true, 'attr_runMe2' => true, 'attr_skip' => true],
            $additionalAttributes
        );

        return FakedValidationModel::createWithAttributes($attributes);
    }

    public function testCreateValidator()
    {
        $model = FakedValidationModel::createWithAttributes(['attr_test1' => 'abc', 'attr_test2' => '2013']);
        /* @var $numberVal NumberValidator */
        $numberVal = TestValidator::createValidator('number', $model, ['attr_test1']);
        $this->assertInstanceOf(NumberValidator::className(), $numberVal);
        $numberVal = TestValidator::createValidator('integer', $model, ['attr_test2']);
        $this->assertInstanceOf(NumberValidator::className(), $numberVal);
        $this->assertTrue($numberVal->integerOnly);
        $val = TestValidator::createValidator(
            'boolean',
            $model,
            ['attr_test1', 'attr_test2'],
            ['on' => ['a', 'b']]
        );
        $this->assertInstanceOf(BooleanValidator::className(), $val);
        $this->assertSame(['a', 'b'], $val->on);
        $this->assertSame(['attr_test1', 'attr_test2'], $val->attributes);
        $val = TestValidator::createValidator(
            'boolean',
            $model,
            ['attr_test1', 'attr_test2'],
            ['on' => ['a', 'b'], 'except' => ['c', 'd', 'e']]
        );
        $this->assertInstanceOf(BooleanValidator::className(), $val);
        $this->assertSame(['a', 'b'], $val->on);
        $this->assertSame(['c', 'd', 'e'], $val->except);
        $val = TestValidator::createValidator('inlineVal', $model, ['val_attr_a'], ['params' => ['foo' => 'bar']]);
        $this->assertInstanceOf(InlineValidator::className(), $val);
        $this->assertSame('inlineVal', $val->method);
        $this->assertSame(['foo' => 'bar'], $val->params);
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/14370
     */
    public function testCreateBuiltInValidatorWithSameNameFunction()
    {
        $model = new ValidatorTestFunctionModel();

        $validator = TestValidator::createValidator('required', $model, ['firstAttribute']);

        $this->assertInstanceOf(RequiredValidator::className(), $validator);
    }

    public function testValidate()
    {
        $val = new TestValidator(['attributes' => ['attr_runMe1', 'attr_runMe2']]);
        $model = $this->getTestModel();
        $val->validateAttributes($model);
        $this->assertTrue($val->isAttributeValidated('attr_runMe1'));
        $this->assertTrue($val->isAttributeValidated('attr_runMe2'));
        $this->assertFalse($val->isAttributeValidated('attr_skip'));
    }

    public function testValidateWithAttributeIntersect()
    {
        $val = new TestValidator(['attributes' => ['attr_runMe1', 'attr_runMe2']]);
        $model = $this->getTestModel();
        $val->validateAttributes($model, ['attr_runMe1']);
        $this->assertTrue($val->isAttributeValidated('attr_runMe1'));
        $this->assertFalse($val->isAttributeValidated('attr_runMe2'));
        $this->assertFalse($val->isAttributeValidated('attr_skip'));
    }

    public function testValidateWithEmptyAttributes()
    {
        $val = new TestValidator();
        $model = $this->getTestModel();
        $val->validateAttributes($model, ['attr_runMe1']);
        $this->assertFalse($val->isAttributeValidated('attr_runMe1'));
        $this->assertFalse($val->isAttributeValidated('attr_runMe2'));
        $this->assertFalse($val->isAttributeValidated('attr_skip'));
        $val->validateAttributes($model);
        $this->assertFalse($val->isAttributeValidated('attr_runMe1'));
        $this->assertFalse($val->isAttributeValidated('attr_runMe2'));
        $this->assertFalse($val->isAttributeValidated('attr_skip'));
    }

    public function testValidateWithError()
    {
        $val = new TestValidator(['attributes' => ['attr_runMe1', 'attr_runMe2'], 'skipOnError' => false]);
        $model = $this->getTestModel();
        $val->validateAttributes($model);
        $this->assertTrue($val->isAttributeValidated('attr_runMe1'));
        $this->assertTrue($val->isAttributeValidated('attr_runMe2'));
        $this->assertFalse($val->isAttributeValidated('attr_skip'));
        $this->assertEquals(1, $val->countAttributeValidations('attr_runMe2'));
        $this->assertEquals(1, $val->countAttributeValidations('attr_runMe1'));
        $val->validateAttributes($model, ['attr_runMe2']);
        $this->assertEquals(2, $val->countAttributeValidations('attr_runMe2'));
        $this->assertEquals(1, $val->countAttributeValidations('attr_runMe1'));
        $this->assertEquals(0, $val->countAttributeValidations('attr_skip'));
        $val = new TestValidator(['attributes' => ['attr_runMe1', 'attr_runMe2'], 'skipOnError' => true]);
        $model = $this->getTestModel();
        $val->enableErrorOnValidateAttribute();
        $val->validateAttributes($model);
        $this->assertTrue($val->isAttributeValidated('attr_runMe1'));
        $this->assertTrue($val->isAttributeValidated('attr_runMe2'));
        $this->assertFalse($val->isAttributeValidated('attr_skip'));
        $this->assertEquals(1, $val->countAttributeValidations('attr_runMe1'));
        $this->assertEquals(1, $val->countAttributeValidations('attr_runMe1'));
        $this->assertEquals(0, $val->countAttributeValidations('attr_skip'));
        $val->validateAttributes($model, ['attr_runMe2']);
        $this->assertEquals(1, $val->countAttributeValidations('attr_runMe2'));
        $this->assertEquals(1, $val->countAttributeValidations('attr_runMe1'));
        $this->assertEquals(0, $val->countAttributeValidations('attr_skip'));
    }

    public function testValidateWithEmpty()
    {
        $model = $this->getTestModel(['attr_empty1' => '', 'attr_empty2' => ' ']);
        $attributes = ['attr_runMe1', 'attr_runMe2', 'attr_empty1', 'attr_empty2'];

        $validator = new TestValidator(['attributes' => $attributes, 'skipOnEmpty' => false]);
        $validator->validateAttributes($model);

        $this->assertTrue($validator->isAttributeValidated('attr_runMe1'));
        $this->assertTrue($validator->isAttributeValidated('attr_runMe2'));
        $this->assertTrue($validator->isAttributeValidated('attr_empty1'));
        $this->assertTrue($validator->isAttributeValidated('attr_empty2'));


        $validator = new TestValidator(['attributes' => $attributes, 'skipOnEmpty' => true]);
        $validator->validateAttributes($model);

        $this->assertTrue($validator->isAttributeValidated('attr_runMe1'));
        $this->assertTrue($validator->isAttributeValidated('attr_runMe2'));
        $this->assertFalse($validator->isAttributeValidated('attr_empty1'));
        $this->assertTrue($validator->isAttributeValidated('attr_empty2'));

        $model->attr_empty1 = 'not empty anymore';
        $validator->validateAttributes($model);
        $this->assertTrue($validator->isAttributeValidated('attr_empty1'));
    }

    public function testIsEmpty()
    {
        $val = new TestValidator();
        $this->assertTrue($val->isEmpty(null));
        $this->assertTrue($val->isEmpty([]));
        $this->assertTrue($val->isEmpty(''));
        $this->assertFalse($val->isEmpty(5));
        $this->assertFalse($val->isEmpty(0));
        $this->assertFalse($val->isEmpty(new \stdClass()));
        $this->assertFalse($val->isEmpty('  '));
    }

    public function testValidateValue()
    {
        $this->expectException('yii\base\NotSupportedException');
        $this->expectExceptionMessage(TestValidator::className() . ' does not support validateValue().');
        $val = new TestValidator();
        $val->validate('abc');
    }

    public function testValidateAttribute()
    {
        // Access to validator in inline validation (https://github.com/yiisoft/yii2/issues/6242)

        $model = new FakedValidationModel();
        $val = TestValidator::createValidator('inlineVal', $model, ['val_attr_a'], ['params' => ['foo' => 'bar']]);
        $val->validateAttribute($model, 'val_attr_a');
        $args = $model->getInlineValArgs();

        $this->assertCount(3, $args);
        $this->assertEquals('val_attr_a', $args[0]);
        $this->assertEquals(['foo' => 'bar'], $args[1]);
        $this->assertInstanceOf(InlineValidator::className(), $args[2]);
    }

    public function testClientValidateAttribute()
    {
        $val = new TestValidator();
        $this->assertNull(
            $val->clientValidateAttribute($this->getTestModel(), 'attr_runMe1', [])
        ); //todo pass a view instead of array

        // Access to validator in inline validation (https://github.com/yiisoft/yii2/issues/6242)

        $model = new FakedValidationModel();
        $val = TestValidator::createValidator('inlineVal', $model, ['val_attr_a'], ['params' => ['foo' => 'bar']]);
        $val->clientValidate = 'clientInlineVal';
        $args = $val->clientValidateAttribute($model, 'val_attr_a', null);

        $this->assertCount(3, $args);
        $this->assertEquals('val_attr_a', $args[0]);
        $this->assertEquals(['foo' => 'bar'], $args[1]);
        $this->assertInstanceOf(InlineValidator::className(), $args[2]);
    }

    public function testIsActive()
    {
        $val = new TestValidator();
        $this->assertTrue($val->isActive('scenA'));
        $this->assertTrue($val->isActive('scenB'));
        $val->except = ['scenB'];
        $this->assertTrue($val->isActive('scenA'));
        $this->assertFalse($val->isActive('scenB'));
        $val->on = ['scenC'];
        $this->assertFalse($val->isActive('scenA'));
        $this->assertFalse($val->isActive('scenB'));
        $this->assertTrue($val->isActive('scenC'));
    }

    public function testAddError()
    {
        $val = new TestValidator();
        $m = $this->getTestModel(['attr_msg_val' => 'abc']);
        $val->addError($m, 'attr_msg_val', '{attribute}::{value}');
        $errors = $m->getErrors('attr_msg_val');
        $this->assertEquals('attr_msg_val::abc', $errors[0]);
        $m = $this->getTestModel(['attr_msg_val' => ['bcc']]);
        $val->addError($m, 'attr_msg_val', '{attribute}::{value}');
        $errors = $m->getErrors('attr_msg_val');
        $this->assertEquals('attr_msg_val::array()', $errors[0]);
        $m = $this->getTestModel(['attr_msg_val' => 'abc']);
        $val->addError($m, 'attr_msg_val', '{attribute}::{value}::{param}', ['param' => 'param_value']);
        $errors = $m->getErrors('attr_msg_val');
        $this->assertEquals('attr_msg_val::abc::param_value', $errors[0]);
    }

    public function testGetAttributeNames()
    {
        $validator = new TestValidator();
        $validator->attributes = ['id', 'name', '!email'];
        $this->assertEquals(['id', 'name', 'email'], $validator->getAttributeNames());
    }

    /**
     * @depends  testGetAttributeNames
     */
    public function testGetActiveValidatorsForSafeAttributes()
    {
        $model = $this->getTestModel();
        $validators = $model->getActiveValidators('safe_attr');
        $isFound = false;
        foreach ($validators as $v) {
            if ($v instanceof NumberValidator) {
                $isFound = true;
                break;
            }
        }
        $this->assertTrue($isFound);
    }

    /**
     * Make sure attribute names are calculated dynamically
     * https://github.com/yiisoft/yii2/issues/13979
     * https://github.com/yiisoft/yii2/pull/14413
     */
    public function testAttributeNamesDynamic()
    {
        $model = new DynamicModel(['email1' => 'invalid', 'email2' => 'invalid']);
        $validator = new TestValidator();
        $validator->enableErrorOnValidateAttribute();

        $validator->attributes = ['email1'];
        $model->getValidators()->append($validator);
        $this->assertFalse($model->validate());

        $validator->attributes = ['email2'];
        $model->getValidators()->append($validator);
        $this->assertFalse($model->validate());
    }
}
