<?php

namespace yiiunit\framework\validators;

use yii\validators\EachValidator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

/**
 * @group validators
 */
class EachValidatorTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
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
                '  to be trimmed  '
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
                'text'
            ],
        ]);
        $validator = new EachValidator(['rule' => ['integer']]);

        $validator->allowMessageFromRule = true;
        $validator->validateAttribute($model, 'attr_one');
        $this->assertContains('integer', $model->getFirstError('attr_one'));

        $model->clearErrors();
        $validator->allowMessageFromRule = false;
        $validator->validateAttribute($model, 'attr_one');
        $this->assertNotContains('integer', $model->getFirstError('attr_one'));
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
                ''
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
        $this->assertEquals(3, count($model->attr_one));

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
}
