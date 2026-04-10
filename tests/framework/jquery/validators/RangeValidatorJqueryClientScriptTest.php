<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\jquery\validators;

use PHPUnit\Framework\Attributes\Group;
use Yii;
use yii\validators\RangeValidator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

/**
 * Unit tests for {@see RangeValidator} client validation script.
 */
#[Group('jquery')]
#[Group('validators')]
final class RangeValidatorJqueryClientScriptTest extends TestCase
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

        $modelValidator->attrA = 'apple';

        $validator = new RangeValidator(
            [
                'range' => [
                    'apple',
                    'banana',
                    'cherry',
                ],
            ],
        );

        self::assertSame(
            <<<JS
            yii.validation.range(value, messages, {"range":["apple","banana","cherry"],"not":false,"message":"attrA is invalid.","skipOnEmpty":1});
            JS,
            $validator->clientValidateAttribute($modelValidator, 'attrA', Yii::$app->view),
            'Should return correct validation script.',
        );
        self::assertSame(
            [
                'range' => ['apple', 'banana', 'cherry'],
                'not' => false,
                'message' => 'attrA is invalid.',
                'skipOnEmpty' => 1,
            ],
            $validator->getClientOptions($modelValidator, 'attrA'),
            'Should return correct options array.',
        );

        $validator->validate('someIncorrectValue', $errorMessage);

        self::assertSame(
            'the input value is invalid.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }

    public function testClientValidateAttributeWithAllowArray(): void
    {
        $modelValidator = new FakedValidationModel();

        $modelValidator->attrA = ['red', 'blue'];

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

        self::assertSame(
            <<<JS
            yii.validation.range(value, messages, {"range":["blue","green","red"],"not":false,"message":"attrA is invalid.","skipOnEmpty":1,"allowArray":1});
            JS,
            $validator->clientValidateAttribute($modelValidator, 'attrA', Yii::$app->view),
            'Should return correct validation script.',
        );
        self::assertSame(
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
            'Should return correct options array.',
        );

        $validator->validate(
            [
                'red',
                'yellow',
            ],
            $errorMessage,
        );

        self::assertSame(
            'the input value is invalid.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }

    public function testClientValidateAttributeWithClosureRange(): void
    {
        $modelValidator = new FakedValidationModel();

        $modelValidator->attrA = 'dynamic1';

        $validator = new RangeValidator(
            [
                'range' => static fn(): array => [
                    'dynamic1',
                    'dynamic2',
                    'dynamic3',
                ],
            ],
        );

        self::assertSame(
            <<<JS
            yii.validation.range(value, messages, {"range":["dynamic1","dynamic2","dynamic3"],"not":false,"message":"attrA is invalid.","skipOnEmpty":1});
            JS,
            $validator->clientValidateAttribute($modelValidator, 'attrA', Yii::$app->view),
            'Should return correct validation script.',
        );
        self::assertSame(
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
            'Should return correct options array.',
        );

        $validator->validate('someIncorrectValue', $errorMessage);

        self::assertSame(
            'the input value is invalid.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }

    public function testClientValidateAttributeWithUseJqueryFalse(): void
    {
        Yii::$app->useJquery = false;

        $modelValidator = new FakedValidationModel();

        $modelValidator->attrA = 'option1';

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

        self::assertNull(
            $validator->clientScript,
            "Should be 'null' when 'useJquery' is 'false'.",
        );
        self::assertNull(
            $validator->clientValidateAttribute($modelValidator, 'attrA', Yii::$app->view),
            "Should return 'null' value.",
        );
        self::assertEmpty(
            $validator->getClientOptions($modelValidator, 'attrA'),
            'Should return an empty array.',
        );

        $validator->validate('someIncorrectValue', $errorMessage);

        self::assertSame(
            'the input value is invalid.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }
}
