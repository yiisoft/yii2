<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\validators;

use yii\validators\EachValidator;
use yiiunit\data\base\ArrayAccessObject;
use yiiunit\data\base\Speaker;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\data\validators\models\ValidatorTestTypedPropModel;
use yiiunit\data\validators\models\ValidatorTestEachAndInlineMethodModel;
use yiiunit\TestCase;

/**
 * @group validators
 */
class EachValidatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // destroy application, Validator must work without Yii::$app
        $this->destroyApplication();
    }

    public function testArrayFormat()
    {
        $validator = new EachValidator(['rule' => ['required']]);

        $this->assertFalse($validator->validate('not array'));
        $this->assertTrue($validator->validate(['value']));
    }

    /**
     * @depends testArrayFormat
     */
    public function testValidate()
    {
        $validator = new EachValidator(['rule' => ['integer']]);

        $this->assertTrue($validator->validate([1, 3, 8]));
        $this->assertFalse($validator->validate([1, 'text', 8]));
    }

    /**
     * @depends testArrayFormat
     */
    public function testFilter()
    {
        $model = FakedValidationModel::createWithAttributes([
            'attr_one' => [
                '  to be trimmed  ',
            ],
        ]);
        $validator = new EachValidator(['rule' => ['trim']]);
        $validator->validateAttribute($model, 'attr_one');
        $this->assertEquals('to be trimmed', $model->attr_one[0]);
    }

    /**
     * @depends testValidate
     */
    public function testAllowMessageFromRule()
    {
        $model = FakedValidationModel::createWithAttributes([
            'attr_one' => [
                'text',
            ],
        ]);
        $validator = new EachValidator(['rule' => ['integer']]);

        $validator->allowMessageFromRule = true;
        $validator->validateAttribute($model, 'attr_one');
        $this->assertStringContainsString('integer', $model->getFirstError('attr_one'));

        $model->clearErrors();
        $validator->allowMessageFromRule = false;
        $validator->validateAttribute($model, 'attr_one');
        $this->assertStringNotContainsString('integer', $model->getFirstError('attr_one'));
    }

    /**
     * @depends testValidate
     */
    public function testCustomMessageValue()
    {
        $model = FakedValidationModel::createWithAttributes([
            'attr_one' => [
                'TEXT',
            ],
        ]);
        $validator = new EachValidator(['rule' => ['integer', 'message' => '{value} is not an integer']]);

        $validator->validateAttribute($model, 'attr_one');
        $this->assertSame('TEXT is not an integer', $model->getFirstError('attr_one'));

        $model->clearErrors();
        $validator->allowMessageFromRule = false;
        $validator->message = '{value} is invalid';
        $validator->validateAttribute($model, 'attr_one');
        $this->assertEquals('TEXT is invalid', $model->getFirstError('attr_one'));
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/10825
     *
     * @depends testValidate
     */
    public function testSkipOnEmpty()
    {
        $validator = new EachValidator(['rule' => ['integer', 'skipOnEmpty' => true]]);
        $this->assertTrue($validator->validate(['']));

        $validator = new EachValidator(['rule' => ['integer', 'skipOnEmpty' => false]]);
        $this->assertFalse($validator->validate(['']));

        $model = FakedValidationModel::createWithAttributes([
            'attr_one' => [
                '',
            ],
        ]);
        $validator = new EachValidator(['rule' => ['integer', 'skipOnEmpty' => true]]);
        $validator->validateAttribute($model, 'attr_one');
        $this->assertFalse($model->hasErrors('attr_one'));

        $model->clearErrors();
        $validator = new EachValidator(['rule' => ['integer', 'skipOnEmpty' => false]]);
        $validator->validateAttribute($model, 'attr_one');
        $this->assertTrue($model->hasErrors('attr_one'));
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/9935
     *
     * @depends testValidate
     */
    public function testCompare()
    {
        $model = FakedValidationModel::createWithAttributes([
            'attr_one' => [
                'value1',
                'value2',
                'value3',
            ],
            'attr_two' => 'value2',
        ]);
        $validator = new EachValidator(['rule' => ['compare', 'compareAttribute' => 'attr_two']]);
        $validator->validateAttribute($model, 'attr_one');
        $this->assertNotEmpty($model->getErrors('attr_one'));
        $this->assertCount(3, $model->attr_one);

        $model = FakedValidationModel::createWithAttributes([
            'attr_one' => [
                'value1',
                'value2',
                'value3',
            ],
            'attr_two' => 'value4',
        ]);
        $validator = new EachValidator(['rule' => ['compare', 'compareAttribute' => 'attr_two', 'operator' => '!=']]);
        $validator->validateAttribute($model, 'attr_one');
        $this->assertEmpty($model->getErrors('attr_one'));
    }

    /**
     * @depends testValidate
     */
    public function testStopOnFirstError()
    {
        $model = FakedValidationModel::createWithAttributes([
            'attr_one' => [
                'one', 2, 'three',
            ],
        ]);
        $validator = new EachValidator(['rule' => ['integer']]);

        $validator->stopOnFirstError = true;
        $validator->validateAttribute($model, 'attr_one');
        $this->assertCount(1, $model->getErrors('attr_one'));

        $model->clearErrors();
        $validator->stopOnFirstError = false;
        $validator->validateAttribute($model, 'attr_one');
        $this->assertCount(2, $model->getErrors('attr_one'));
    }

    public function testValidateArrayAccess()
    {
        $model = FakedValidationModel::createWithAttributes([
            'attr_array' => new ArrayAccessObject([1,2,3]),
        ]);

        $validator = new EachValidator(['rule' => ['integer']]);
        $validator->validateAttribute($model, 'attr_array');
        $this->assertFalse($model->hasErrors('array'));

        $this->assertTrue($validator->validate($model->attr_array));
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/17810
     *
     * Do not reuse model property for storing value
     * of different type during validation.
     * (ie: public array $dummy; where $dummy is array of booleans,
     * validator will try to assign these booleans one by one to $dummy)
     *
     * @requires PHP >= 7.4
     */
    public function testTypedProperties()
    {
        $model = new ValidatorTestTypedPropModel();

        $validator = new EachValidator(['rule' => ['boolean']]);
        $validator->validateAttribute($model, 'arrayTypedProperty');
        $this->assertFalse($model->hasErrors('arrayTypedProperty'));
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/18011
     */
    public function testErrorMessage()
    {
        $model = new Speaker();
        $model->customLabel = ['invalid_ip'];

        $validator = new EachValidator(['rule' => ['ip']]);
        $validator->validateAttribute($model, 'customLabel');
        $validator->validateAttribute($model, 'firstName');

        $this->assertEquals('This is the custom label must be a valid IP address.', $model->getFirstError('customLabel'));
        $this->assertEquals('First Name is invalid.', $model->getFirstError('firstName'));
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/18051
     */
    public function testCustomMethod()
    {
        $model = new Speaker();
        $model->firstName = ['a', 'b'];

        $validator = new EachValidator(['rule' => ['customValidatingMethod']]);
        $validator->validateAttribute($model, 'firstName');

        $this->assertEquals('Custom method error', $model->getFirstError('firstName'));
        // make sure each value of attribute array is checked separately
        $this->assertEquals(['a', 'b'], $model->getCheckedValues());
        // make sure original array is restored at the end
        $this->assertEquals(['a', 'b'], $model->firstName);
    }

    public function testAnonymousMethod()
    {
        $model = new ValidatorTestEachAndInlineMethodModel();

        $model->validate();
        $this->assertFalse($model->hasErrors('arrayProperty'));
    }
}
