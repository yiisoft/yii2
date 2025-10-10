<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\jquery\validators;

use Yii;
use yii\validators\EmailValidator;
use yii\web\View;
use yiiunit\data\validators\models\FakedValidationModel;

/**
 * @group jquery
 * @group validators
 */
final class EmailValidatorJqueryClientScriptTest extends \yiiunit\TestCase
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
        $validator = new EmailValidator();

        $modelValidator->attrA = 'test@example.com';

        $this->assertSame(
            'yii.validation.email(value, messages, {"pattern":' . $validator->pattern . ',"fullPattern":' .
            $validator->fullPattern . ',"allowName":false,"message":"attrA is not a valid email address.",' .
            '"enableIDN":false,"skipOnEmpty":1});',
            $validator->clientValidateAttribute($modelValidator, 'attrA', new View()),
            "'clientValidateAttribute()' method should return correct validation script.",
        );

        $clientOptions = $validator->getClientOptions($modelValidator, 'attrA');

        $clientOptions['pattern'] = (string) ($clientOptions['pattern'] ?? '');
        $clientOptions['fullPattern'] = (string) ($clientOptions['fullPattern'] ?? '');

        $this->assertSame(
            [
                'pattern' => $validator->pattern,
                'fullPattern' => $validator->fullPattern,
                'allowName' => false,
                'message' => 'attrA is not a valid email address.',
                'enableIDN' => false,
                'skipOnEmpty' => 1,
            ],
            $clientOptions,
            "'getClientOptions()' method should return correct options array.",
        );

        $validator->validate('invalid-email', $errorMessage);

        $this->assertSame(
            'the input value is not a valid email address.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }

    public function testClientValidateAttributeWithEnableIDN(): void
    {
        $modelValidator = new FakedValidationModel();
        $validator = new EmailValidator(['enableIDN' => true]);

        $this->assertSame(
            'yii.validation.email(value, messages, {"pattern":' . $validator->pattern . ',"fullPattern":' .
            $validator->fullPattern . ',"allowName":false,"message":"attrA is not a valid email address.",' .
            '"enableIDN":true,"skipOnEmpty":1});',
            $validator->clientValidateAttribute($modelValidator, 'attrA', new View()),
            "'clientValidateAttribute()' method should return correct validation script.",
        );

        $clientOptions = $validator->getClientOptions($modelValidator, 'attrA');

        $clientOptions['pattern'] = (string) ($clientOptions['pattern'] ?? '');
        $clientOptions['fullPattern'] = (string) ($clientOptions['fullPattern'] ?? '');

        $this->assertSame(
            [
                'pattern' => $validator->pattern,
                'fullPattern' => $validator->fullPattern,
                'allowName' => false,
                'message' => 'attrA is not a valid email address.',
                'enableIDN' => true,
                'skipOnEmpty' => 1,
            ],
            $clientOptions,
            "'getClientOptions()' method should return correct options array.",
        );

        $validator->validate('invalid-email', $errorMessage);

        $this->assertSame(
            'the input value is not a valid email address.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }

    public function testClientValidateAttributeWithUseJqueryFalse(): void
    {
        Yii::$app->useJquery = false;

        $modelValidator = new FakedValidationModel();
        $validator = new EmailValidator(
            [
                'allowName' => true,
                'enableIDN' => false,
            ],
        );

        $modelValidator->attrA = 'test@example.com';

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

        $validator->validate('invalid-email', $errorMessage);

        $this->assertSame(
            'the input value is not a valid email address.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }
}
