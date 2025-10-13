<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\jquery\validators;

use Yii;
use yii\validators\RegularExpressionValidator;
use yii\web\View;
use yiiunit\data\validators\models\FakedValidationModel;

/**
 * @group jquery
 * @group validators
 */
final class RegularExpressionValidatorJqueryClientScriptTest extends \yiiunit\TestCase
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
        $validator = new RegularExpressionValidator(['pattern' => '/^[a-zA-Z0-9]+$/']);

        $modelValidator->attrA = 'apple';

        $this->assertSame(
            'yii.validation.regularExpression(value, messages, {"pattern":/^[a-zA-Z0-9]+$/,"not":false,' .
            '"message":"attrA is invalid.","skipOnEmpty":1});',
            $validator->clientValidateAttribute($modelValidator, 'attrA', new View()),
            "'clientValidateAttribute()' method should return correct validation script.",
        );

        $clientOptions = $validator->getClientOptions($modelValidator, 'attrA');

        $clientOptions['pattern'] = (string) ($clientOptions['pattern'] ?? '');

        $this->assertSame(
            [
                'pattern' => '/^[a-zA-Z0-9]+$/',
                'not' => false,
                'message' => 'attrA is invalid.',
                'skipOnEmpty' => 1,
            ],
            $clientOptions,
            "'getClientOptions()' method should return correct options array.",
        );

        $validator->validate('someIncorrectValue!', $errorMessage);

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
        $validator = new RegularExpressionValidator(['pattern' => '/^[a-zA-Z0-9]+$/']);

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

        $validator->validate('someIncorrectValue!', $errorMessage);

        $this->assertSame(
            'the input value is invalid.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }
}
