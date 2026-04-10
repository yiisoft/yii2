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
use yii\validators\BooleanValidator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

/**
 * Unit tests for {@see BooleanValidator} client validation script.
 */
#[Group('jquery')]
#[Group('validators')]
final class BooleanValidatorJqueryClientScriptTest extends TestCase
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

        $modelValidator->attrA = true;

        $validator = new BooleanValidator(
            [
                'trueValue' => true,
                'falseValue' => false,
                'strict' => true,
            ],
        );

        self::assertSame(
            <<<JS
            yii.validation.boolean(value, messages, {"trueValue":true,"falseValue":false,"message":"attrA must be either \u0022true\u0022 or \u0022false\u0022.","skipOnEmpty":1,"strict":1});
            JS,
            $validator->clientValidateAttribute($modelValidator, 'attrA', Yii::$app->view),
            'Should return correct validation script.',
        );
        self::assertSame(
            [
                'trueValue' => true,
                'falseValue' => false,
                'message' => 'attrA must be either "true" or "false".',
                'skipOnEmpty' => 1,
                'strict' => 1,
            ],
            $validator->getClientOptions($modelValidator, 'attrA'),
            'Should return correct options array.',
        );

        $validator->validate('someIncorrectValue', $errorMessage);

        self::assertSame(
            'the input value must be either "true" or "false".',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }

    public function testClientValidateAttributeWithUseJqueryFalse(): void
    {
        Yii::$app->useJquery = false;

        $modelValidator = new FakedValidationModel();

        $modelValidator->attrA = true;

        $validator = new BooleanValidator(
            [
                'trueValue' => true,
                'falseValue' => false,
                'strict' => true,
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
            'Sould return an empty array.',
        );

        $validator->validate('someIncorrectValue', $errorMessage);

        self::assertSame(
            'the input value must be either "true" or "false".',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }
}
