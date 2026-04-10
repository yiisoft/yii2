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
use yii\validators\StringValidator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

/**
 * Unit tests for {@see StringValidator} client validation script.
 */
#[Group('jquery')]
#[Group('validators')]
final class StringValidatorJqueryClientScriptTest extends TestCase
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

        $modelValidator->attrA = 'test';

        $validator = new StringValidator(
            [
                'min' => 3,
                'max' => 10,
            ],
        );

        self::assertSame(
            <<<JS
            yii.validation.string(value, messages, {"message":"attrA must be a string.","min":3,"tooShort":"attrA should contain at least 3 characters.","max":10,"tooLong":"attrA should contain at most 10 characters.","skipOnEmpty":1});
            JS,
            $validator->clientValidateAttribute($modelValidator, 'attrA', Yii::$app->view),
            'Should return correct validation script.',
        );
        self::assertSame(
            [
                'message' => 'attrA must be a string.',
                'min' => 3,
                'tooShort' => 'attrA should contain at least 3 characters.',
                'max' => 10,
                'tooLong' => 'attrA should contain at most 10 characters.',
                'skipOnEmpty' => 1,
            ],
            $validator->getClientOptions($modelValidator, 'attrA'),
            'Should return correct options array.',
        );

        $validator->validate('so', $errorMessage);

        self::assertSame(
            'the input value should contain at least 3 characters.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }

    public function testClientValidateAttributeWithLength(): void
    {
        $modelValidator = new FakedValidationModel();

        $modelValidator->attrA = 'hello';

        $validator = new StringValidator(['length' => 5]);

        self::assertSame(
            <<<JS
            yii.validation.string(value, messages, {"message":"attrA must be a string.","is":5,"notEqual":"attrA should contain 5 characters.","skipOnEmpty":1});
            JS,
            $validator->clientValidateAttribute($modelValidator, 'attrA', Yii::$app->view),
            'Should return correct validation script.',
        );
        self::assertSame(
            [
                'message' => 'attrA must be a string.',
                'is' => 5,
                'notEqual' => 'attrA should contain 5 characters.',
                'skipOnEmpty' => 1,
            ],
            $validator->getClientOptions($modelValidator, 'attrA'),
            'Should return correct options array.',
        );

        $validator->validate('someIncorrectValue', $errorMessage);

        self::assertSame(
            'the input value should contain 5 characters.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }

    public function testClientValidateAttributeWithUseJqueryFalse(): void
    {
        Yii::$app->useJquery = false;

        $modelValidator = new FakedValidationModel();

        $modelValidator->attrA = 'test';

        $validator = new StringValidator(
            [
                'min' => 3,
                'max' => 10,
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

        $validator->validate('so', $errorMessage);

        self::assertSame(
            'the input value should contain at least 3 characters.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }
}
