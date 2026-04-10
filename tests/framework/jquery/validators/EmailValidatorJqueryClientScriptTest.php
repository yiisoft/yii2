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
use yii\validators\EmailValidator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

/**
 * Unit tests for {@see EmailValidator} client validation script.
 */
#[Group('jquery')]
#[Group('validators')]
final class EmailValidatorJqueryClientScriptTest extends TestCase
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

        $modelValidator->attrA = 'test@example.com';

        $validator = new EmailValidator();

        self::assertSame(
            <<<JS
            yii.validation.email(value, messages, {"pattern":{$validator->pattern},"fullPattern":{$validator->fullPattern},"allowName":false,"message":"attrA is not a valid email address.","enableIDN":false,"skipOnEmpty":1});
            JS,
            $validator->clientValidateAttribute($modelValidator, 'attrA', Yii::$app->view),
            'Should return correct validation script.',
        );

        $clientOptions = $validator->getClientOptions($modelValidator, 'attrA');

        $clientOptions['pattern'] = (string) ($clientOptions['pattern'] ?? '');
        $clientOptions['fullPattern'] = (string) ($clientOptions['fullPattern'] ?? '');

        self::assertSame(
            [
                'pattern' => $validator->pattern,
                'fullPattern' => $validator->fullPattern,
                'allowName' => false,
                'message' => 'attrA is not a valid email address.',
                'enableIDN' => false,
                'skipOnEmpty' => 1,
            ],
            $clientOptions,
            'Should return correct options array.',
        );

        $validator->validate('invalid-email', $errorMessage);

        self::assertSame(
            'the input value is not a valid email address.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }

    public function testClientValidateAttributeWithEnableIDN(): void
    {
        $modelValidator = new FakedValidationModel();

        $validator = new EmailValidator(['enableIDN' => true]);

        self::assertSame(
            <<<JS
            yii.validation.email(value, messages, {"pattern":{$validator->pattern},"fullPattern":{$validator->fullPattern},"allowName":false,"message":"attrA is not a valid email address.","enableIDN":true,"skipOnEmpty":1});
            JS,
            $validator->clientValidateAttribute($modelValidator, 'attrA', Yii::$app->view),
            'Should return correct validation script.',
        );

        $clientOptions = $validator->getClientOptions($modelValidator, 'attrA');

        $clientOptions['pattern'] = (string) ($clientOptions['pattern'] ?? '');
        $clientOptions['fullPattern'] = (string) ($clientOptions['fullPattern'] ?? '');

        self::assertSame(
            [
                'pattern' => $validator->pattern,
                'fullPattern' => $validator->fullPattern,
                'allowName' => false,
                'message' => 'attrA is not a valid email address.',
                'enableIDN' => true,
                'skipOnEmpty' => 1,
            ],
            $clientOptions,
            'Should return correct options array.',
        );

        $validator->validate('invalid-email', $errorMessage);

        self::assertSame(
            'the input value is not a valid email address.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }

    public function testClientValidateAttributeWithUseJqueryFalse(): void
    {
        Yii::$app->useJquery = false;

        $modelValidator = new FakedValidationModel();

        $modelValidator->attrA = 'test@example.com';

        $validator = new EmailValidator(
            [
                'allowName' => true,
                'enableIDN' => false,
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

        $validator->validate('invalid-email', $errorMessage);

        self::assertSame(
            'the input value is not a valid email address.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }
}
