<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\jquery\validators;

use Yii;
use yii\validators\NumberValidator;
use yii\web\View;
use yiiunit\data\validators\models\FakedValidationModel;

/**
 * @group jquery
 * @group validators
 */
final class NumberValidatorJqueryClientScriptTest extends \yiiunit\TestCase
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
        $validator = new NumberValidator();

        $modelValidator->attrA = 123.45;

        $this->assertSame(
            'yii.validation.number(value, messages, {"pattern":/^[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?$/,' .
            '"message":"attrA must be a number.","skipOnEmpty":1});',
            $validator->clientValidateAttribute($modelValidator, 'attrA', new View()),
            "'clientValidateAttribute()' method should return correct validation script.",
        );

        $clientOptions = $validator->getClientOptions($modelValidator, 'attrA');

        $clientOptions['pattern'] = (string) ($clientOptions['pattern'] ?? '');

        $this->assertSame(
            [
                'pattern' => $validator->numberPattern,
                'message' => 'attrA must be a number.',
                'skipOnEmpty' => 1,
            ],
            $clientOptions,
            "'getClientOptions()' method should return correct options array.",
        );

        $validator->validate('invalid-number', $errorMessage);

        $this->assertSame(
            'the input value must be a number.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/3118
     */
    public function testClientValidateComparison(): void
    {
        $val = new NumberValidator(
            [
                'min' => 5,
                'max' => 10,
            ],
        );
        $model = new FakedValidationModel();

        $js = $val->clientValidateAttribute(
            $model,
            'attr_number',
            new View(['assetBundles' => ['yii\validators\ValidationAsset' => true]]),
        );

        $this->assertStringContainsString('"min":5', $js);
        $this->assertStringContainsString('"max":10', $js);

        $val = new NumberValidator(
            [
                'min' => '5',
                'max' => '10',
            ],
        );
        $model = new FakedValidationModel();

        $js = $val->clientValidateAttribute(
            $model,
            'attr_number',
            new View(['assetBundles' => ['yii\validators\ValidationAsset' => true]]),
        );

        $this->assertStringContainsString('"min":5', $js);
        $this->assertStringContainsString('"max":10', $js);

        $val = new NumberValidator(
            [
                'min' => 5.65,
                'max' => 13.37,
            ],
        );
        $model = new FakedValidationModel();

        $js = $val->clientValidateAttribute(
            $model,
            'attr_number',
            new View(['assetBundles' => ['yii\validators\ValidationAsset' => true]]),
        );

        $this->assertStringContainsString('"min":5.65', $js);
        $this->assertStringContainsString('"max":13.37', $js);

        $val = new NumberValidator(
            [
                'min' => '5.65',
                'max' => '13.37',
            ],
        );
        $model = new FakedValidationModel();

        $js = $val->clientValidateAttribute(
            $model,
            'attr_number',
            new View(['assetBundles' => ['yii\validators\ValidationAsset' => true]]),
        );

        $this->assertStringContainsString('"min":5.65', $js);
        $this->assertStringContainsString('"max":13.37', $js);
    }

    public function testClientValidateAttributeWithUseJqueryFalse(): void
    {
        Yii::$app->useJquery = false;

        $modelValidator = new FakedValidationModel();
        $validator = new NumberValidator(
            [
                'min' => 10,
                'max' => 100,
            ],
        );

        $modelValidator->attrA = 50;

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

        $validator->validate(5, $errorMessage);

        $this->assertSame(
            'the input value must be no less than 10.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }
}
