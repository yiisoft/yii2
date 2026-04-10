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
use yii\validators\RegularExpressionValidator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

/**
 * Unit tests for {@see RegularExpressionValidator} client validation script.
 */
#[Group('jquery')]
#[Group('validators')]
final class RegularExpressionValidatorJqueryClientScriptTest extends TestCase
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

        $validator = new RegularExpressionValidator(['pattern' => '/^[a-zA-Z0-9]+$/']);

        self::assertSame(
            <<<JS
            yii.validation.regularExpression(value, messages, {"pattern":/^[a-zA-Z0-9]+$/,"not":false,"message":"attrA is invalid.","skipOnEmpty":1});
            JS,
            $validator->clientValidateAttribute($modelValidator, 'attrA', Yii::$app->view),
            'Should return correct validation script.',
        );

        $clientOptions = $validator->getClientOptions($modelValidator, 'attrA');

        $clientOptions['pattern'] = (string) ($clientOptions['pattern'] ?? '');

        self::assertSame(
            [
                'pattern' => '/^[a-zA-Z0-9]+$/',
                'not' => false,
                'message' => 'attrA is invalid.',
                'skipOnEmpty' => 1,
            ],
            $clientOptions,
            'Should return correct options array.',
        );

        $validator->validate('someIncorrectValue!', $errorMessage);

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

        $validator = new RegularExpressionValidator(['pattern' => '/^[a-zA-Z0-9]+$/']);

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

        $validator->validate('someIncorrectValue!', $errorMessage);

        self::assertSame(
            'the input value is invalid.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }
}
