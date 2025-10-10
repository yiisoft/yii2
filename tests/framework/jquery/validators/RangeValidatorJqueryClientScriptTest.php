<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\jquery\validators;

use Yii;
use yii\validators\RangeValidator;
use yii\web\View;
use yiiunit\data\validators\models\FakedValidationModel;

/**
 * @group jquery
 * @group validators
 */
final class RangeValidatorJqueryClientScriptTest extends \yiiunit\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockWebApplication();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->destroyApplication();
    }

    public function testClientValidateAttribute(): void
    {
        $modelValidator = new FakedValidationModel();
        $validator = new RangeValidator(
            [
                'range' => [
                    'apple',
                    'banana',
                    'cherry',
                ],
            ],
        );

        $modelValidator->attrA = 'apple';

        $this->assertSame(
            'yii.validation.range(value, messages, {"range":["apple","banana","cherry"],"not":false,' .
            '"message":"attrA is invalid.","skipOnEmpty":1});',
            $validator->clientValidateAttribute($modelValidator, 'attrA', new View()),
            "'clientValidateAttribute()' method should return correct validation script.",
        );
        $this->assertSame(
            [
                'range' => ['apple', 'banana', 'cherry'],
                'not' => false,
                'message' => 'attrA is invalid.',
                'skipOnEmpty' => 1,
            ],
            $validator->getClientOptions($modelValidator, 'attrA'),
            "'getClientOptions()' method should return correct options array.",
        );

        $validator->validate('someIncorrectValue', $errorMessage);

        $this->assertSame(
            'the input value is invalid.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }

    public function testClientValidateAttributeWithAllowArray(): void
    {
        $modelValidator = new FakedValidationModel();
        $validator = new RangeValidator(
            [
                'range' => [
                    'blue',
                    'green',
                    'red',
                ],
                'allowArray' => true,
            ],
        );

        $modelValidator->attrA = ['red', 'blue'];

        $this->assertSame(
            'yii.validation.range(value, messages, {"range":["blue","green","red"],"not":false,' .
            '"message":"attrA is invalid.","skipOnEmpty":1,"allowArray":1});',
            $validator->clientValidateAttribute($modelValidator, 'attrA', new View()),
            "'clientValidateAttribute()' method should return correct validation script.",
        );
        $this->assertSame(
            [
                'range' => [
                    'blue',
                    'green',
                    'red',
                ],
                'not' => false,
                'message' => 'attrA is invalid.',
                'skipOnEmpty' => 1,
                'allowArray' => 1,
            ],
            $validator->getClientOptions($modelValidator, 'attrA'),
            "'getClientOptions()' method should return correct options array.",
        );

        $validator->validate(
            [
                'red',
                'yellow',
            ],
            $errorMessage,
        );

        $this->assertSame(
            'the input value is invalid.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }

    public function testClientValidateAttributeWithClosureRange(): void
    {
        $modelValidator = new FakedValidationModel();
        $validator = new RangeValidator(
            [
                'range' => static fn(): array => [
                    'dynamic1',
                    'dynamic2',
                    'dynamic3',
                ],
            ],
        );

        $modelValidator->attrA = 'dynamic1';

        $this->assertSame(
            'yii.validation.range(value, messages, {"range":["dynamic1","dynamic2","dynamic3"],"not":false,' .
            '"message":"attrA is invalid.","skipOnEmpty":1});',
            $validator->clientValidateAttribute($modelValidator, 'attrA', new View()),
            "'clientValidateAttribute()' method should return correct validation script.",
        );
        $this->assertSame(
            [
                'range' => [
                    'dynamic1',
                    'dynamic2',
                    'dynamic3',
                ],
                'not' => false,
                'message' => 'attrA is invalid.',
                'skipOnEmpty' => 1,
            ],
            $validator->getClientOptions($modelValidator, 'attrA'),
            "'getClientOptions()' method should return correct options array.",
        );

        $validator->validate('someIncorrectValue', $errorMessage);

        $this->assertSame(
            'the input value is invalid.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }

    public function testClientValidateAttributeWithUseJqueryFalse(): void
    {
        Yii::$app->useJquery = false;

        $modelValidator = new FakedValidationModel();
        $validator = new RangeValidator(
            [
                'range' => [
                    'option1',
                    'option2',
                    'option3',
                ],
                'not' => false,
            ],
        );

        $modelValidator->attrA = 'option1';

        $this->assertNull(
            $validator->clientScript,
            "'ClientScript' property should be 'null' when 'useJquery' is 'false'.",
        );
        $this->assertNull(
            $validator->clientValidateAttribute($modelValidator, 'attrA', new View()),
            "'clientValidateAttribute()' method should return 'null' value.",
        );
        $this->assertEmpty(
            $validator->getClientOptions($modelValidator, 'attrA'),
            "'getClientOptions()' method should return an empty array.",
        );

        $validator->validate('someIncorrectValue', $errorMessage);

        $this->assertSame(
            'the input value is invalid.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }
}
