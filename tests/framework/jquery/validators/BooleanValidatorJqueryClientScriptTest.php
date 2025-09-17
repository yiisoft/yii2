<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\jquery\validators;

use Yii;
use yii\validators\BooleanValidator;
use yii\web\View;
use yiiunit\data\validators\models\FakedValidationModel;

/**
 * @group jquery
 */
final class BooleanValidatorJqueryClientScriptTest extends \yiiunit\TestCase
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
        $validator = new BooleanValidator(
            [
                'trueValue' => true,
                'falseValue' => false,
                'strict' => true,
            ],
        );
        $view = new View();

        $validator->validate('someIncorrectValue', $errorMessage);

        $this->assertEquals(
            'the input value must be either "true" or "false".',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );

        $modelValidator = new FakedValidationModel();

        $modelValidator->attrA = true;

        $this->assertSame(
            'yii.validation.boolean(value, messages, {"trueValue":true,"falseValue":false,"message":"attrB must be either \u0022true\u0022 or \u0022false\u0022.","skipOnEmpty":1,"strict":1});',
            $validator->clientValidateAttribute($modelValidator, 'attrB', $view),
            "'clientValidateAttribute()' method should return 'null' value.",
        );
        $this->assertSame(
            [
                'trueValue' => true,
                'falseValue' => false,
                'message' => 'attrA must be either "true" or "false".',
                'skipOnEmpty' => 1,
                'strict' => 1,
            ],
            $validator->getClientOptions($modelValidator, 'attrA'),
            "'getClientOptions()' method should return an empty array.",
        );
    }

    public function testClientValidateAttributeWithUseJqueryFalse(): void
    {
        Yii::$app->useJquery = false;

        $validator = new BooleanValidator(
            [
                'trueValue' => true,
                'falseValue' => false,
                'strict' => true,
            ],
        );
        $view = new View();

        $validator->validate('someIncorrectValue', $errorMessage);

        $this->assertEquals(
            'the input value must be either "true" or "false".',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );

        $modelValidator = new FakedValidationModel();

        $modelValidator->attrA = true;

        $this->assertNull(
            $validator->clientValidateAttribute($modelValidator, 'attrB', $view),
            "'clientValidateAttribute()' method should return 'null' value.",
        );
        $this->assertEmpty(
            $validator->getClientOptions($modelValidator, 'attrA'),
            "'getClientOptions()' method should return an empty array.",
        );
    }
}
