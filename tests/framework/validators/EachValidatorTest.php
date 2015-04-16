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
}