<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\jquery\validators;

use Yii;
use yii\validators\StringValidator;
use yii\web\View;
use yiiunit\data\validators\models\FakedValidationModel;

/**
 * @group jquery
 */
final class StringValidatorJqueryClientScriptTest extends \yiiunit\TestCase
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
        $validator = new StringValidator(
            [
                'min' => 3,
                'max' => 10,
            ],
        );

        $modelValidator->attrA = 'test';

        $this->assertSame(
            'yii.validation.string(value, messages, {"message":"attrA must be a string.","min":3,' .
            '"tooShort":"attrA should contain at least 3 characters.","max":10,' .
            '"tooLong":"attrA should contain at most 10 characters.","skipOnEmpty":1});',
            $validator->clientValidateAttribute($modelValidator, 'attrA', new View()),
            "'clientValidateAttribute()' method should return correct validation script.",
        );
        $this->assertSame(
            [
                'message' => 'attrA must be a string.',
                'min' => 3,
                'tooShort' => 'attrA should contain at least 3 characters.',
                'max' => 10,
                'tooLong' => 'attrA should contain at most 10 characters.',
                'skipOnEmpty' => 1,
            ],
            $validator->getClientOptions($modelValidator, 'attrA'),
            "'getClientOptions()' method should return correct options array.",
        );

        $validator->validate('so', $errorMessage);

        $this->assertSame(
            'the input value should contain at least 3 characters.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }

    public function testClientValidateAttributeWithLength(): void
    {
        $modelValidator = new FakedValidationModel();
        $validator = new StringValidator(['length' => 5]);

        $modelValidator->attrA = 'hello';

        $this->assertSame(
            'yii.validation.string(value, messages, {"message":"attrA must be a string.","is":5,' .
            '"notEqual":"attrA should contain 5 characters.","skipOnEmpty":1});',
            $validator->clientValidateAttribute($modelValidator, 'attrA', new View()),
            "'clientValidateAttribute()' method should return correct validation script.",
        );
        $this->assertSame(
            [
                'message' => 'attrA must be a string.',
                'is' => 5,
                'notEqual' => 'attrA should contain 5 characters.',
                'skipOnEmpty' => 1,
            ],
            $validator->getClientOptions($modelValidator, 'attrA'),
            "'getClientOptions()' method should return correct options array.",
        );

        $validator->validate('someIncorrectValue', $errorMessage);

        $this->assertSame(
            'the input value should contain 5 characters.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }

    public function testClientValidateAttributeWithUseJqueryFalse(): void
    {
        Yii::$app->useJquery = false;

        $modelValidator = new FakedValidationModel();
        $validator = new StringValidator(
            [
                'min' => 3,
                'max' => 10,
            ],
        );

        $modelValidator->attrA = 'test';

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

        $validator->validate('so', $errorMessage);

        $this->assertSame(
            'the input value should contain at least 3 characters.',
            $errorMessage,
            'Failed asserting that the generated error message matches the expected one.',
        );
    }
}
