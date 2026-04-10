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
use yii\jquery\validators\NumberValidatorJqueryClientScript;
use yii\validators\client\ClientValidatorScriptInterface;
use yii\validators\NumberValidator;
use yii\validators\ValidationAsset;
use yii\web\View;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

/**
 * Unit tests for {@see NumberValidator} client validation script.
 */
#[Group('jquery')]
#[Group('validators')]
final class NumberValidatorJqueryClientScriptTest extends TestCase
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

        $modelValidator->attrA = 123.45;

        $validator = new NumberValidator();

        self::assertSame(
            <<<JS
            yii.validation.number(value, messages, {"pattern":/^[-+]?[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?$/,"message":"attrA must be a number.","skipOnEmpty":1});
            JS,
            $validator->clientValidateAttribute($modelValidator, 'attrA', Yii::$app->view),
            'Should return correct validation script.',
        );

        $clientOptions = $validator->getClientOptions($modelValidator, 'attrA');

        $clientOptions['pattern'] = (string) ($clientOptions['pattern'] ?? '');

        self::assertSame(
            [
                'pattern' => $validator->numberPattern,
                'message' => 'attrA must be a number.',
                'skipOnEmpty' => 1,
            ],
            $clientOptions,
            'Should return correct options array.',
        );

        $validator->validate('invalid-number', $errorMessage);

        self::assertSame(
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
            new View(['assetBundles' => [ValidationAsset::class => true]]),
        );

        self::assertStringContainsString(
            '"min":5',
            $js,
            "Failed asserting that the generated client validation script contains the expected 'min' value.",
        );
        self::assertStringContainsString(
            '"max":10',
            $js,
            "Failed asserting that the generated client validation script contains the expected 'max' value.",
        );

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
            new View(['assetBundles' => [ValidationAsset::class => true]]),
        );

        self::assertStringContainsString(
            '"min":5',
            $js,
            "Failed asserting that the generated client validation script contains the expected 'min' value.",
        );
        self::assertStringContainsString(
            '"max":10',
            $js,
            "Failed asserting that the generated client validation script contains the expected 'max' value.",
        );

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
            new View(['assetBundles' => [ValidationAsset::class => true]]),
        );

        self::assertStringContainsString(
            '"min":5.65',
            $js,
            "Failed asserting that the generated client validation script contains the expected 'min' value.",
        );
        self::assertStringContainsString(
            '"max":13.37',
            $js,
            "Failed asserting that the generated client validation script contains the expected 'max' value.",
        );

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
            new View(['assetBundles' => [ValidationAsset::class => true]]),
        );

        self::assertStringContainsString(
            '"min":5.65',
            $js,
            "Failed asserting that the generated client validation script contains the expected 'min' value.",
        );
        self::assertStringContainsString(
            '"max":13.37',
            $js,
            "Failed asserting that the generated client validation script contains the expected 'max' value.",
        );
    }

    public function testClientValidateAttributeWithCustomClientScriptAndUseJqueryFalse(): void
    {
        Yii::$app->useJquery = false;

        $modelValidator = new FakedValidationModel();

        $validator = new NumberValidator(
            [
                'clientScript' => ['class' => NumberValidatorJqueryClientScript::class],
            ],
        );

        self::assertInstanceOf(
            ClientValidatorScriptInterface::class,
            $validator->clientScript,
            'Should instantiate custom clientScript array config via Yii::createObject even when useJquery is false.',
        );
        self::assertInstanceOf(
            NumberValidatorJqueryClientScript::class,
            $validator->clientScript,
            'Should be an instance of NumberValidatorJqueryClientScript.',
        );
    }

    public function testClientValidateAttributeWithUseJqueryFalse(): void
    {
        Yii::$app->useJquery = false;

        $modelValidator = new FakedValidationModel();

        $modelValidator->attrA = 50;

        $validator = new NumberValidator(
            [
                'min' => 10,
                'max' => 100,
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

        $validator->validate(5, $errorMessage);

        self::assertSame(
            'the input value must be no less than 10.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }
}
