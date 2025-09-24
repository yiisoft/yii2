<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\jquery\validators;

use Yii;
use yii\validators\TrimValidator;
use yii\web\View;
use yiiunit\data\validators\models\FakedValidationModel;

/**
 * @group jquery
 */
final class TrimValidatorJqueryClientScriptTest extends \yiiunit\TestCase
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
        $validator = new TrimValidator();

        $modelValidator->attrA = '  test value  ';

        $this->assertSame(
            'value = yii.validation.trim($form, attribute, {"skipOnArray":false,"skipOnEmpty":false,' .
            '"chars":false}, value);',
            $validator->clientValidateAttribute($modelValidator, 'attrA', new View()),
            "'clientValidateAttribute()' method should return correct validation script.",
        );
        $this->assertSame(
            [
                'skipOnArray' => false,
                'skipOnEmpty' => false,
                'chars' => false,
            ],
            $validator->getClientOptions($modelValidator, 'attrA'),
            "'getClientOptions()' method should return correct options array.",
        );

        $validator->validateAttribute($modelValidator, 'attrA');

        $this->assertSame(
            'test value',
            $modelValidator->attrA,
            'Should trim the attribute value.',
        );
    }

    public function testClientValidateAttributeWithSkipOnArray(): void
    {
        $modelValidator = new FakedValidationModel();
        $validator = new TrimValidator(['skipOnArray' => true]);

        $modelValidator->attrA = [
            '  test  ',
            '  value  ',
        ];

        $this->assertEmpty(
            $validator->clientValidateAttribute($modelValidator, 'attrA', new View()),
            "'clientValidateAttribute()' method should return empty string when 'skipOnArray' is 'true' and  value " .
            'is array.',
        );
        $this->assertSame(
            [
                'skipOnArray' => true,
                'skipOnEmpty' => false,
                'chars' => false,
            ],
            $validator->getClientOptions($modelValidator, 'attrA'),
            "'getClientOptions()' method should return correct options array.",
        );

        $validator->validateAttribute($modelValidator, 'attrA');

        $this->assertSame(
            [
                '  test  ',
                '  value  ',
            ],
            $modelValidator->attrA,
            "Should skip array values when 'skipOnArray' is 'true'.",
        );
    }

    public function testClientValidateAttributeWithUseJqueryFalse(): void
    {
        Yii::$app->useJquery = false;

        $modelValidator = new FakedValidationModel();
        $validator = new TrimValidator(['chars' => '/-']);

        $modelValidator->attrA = '//test-value--';

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

        $validator->validateAttribute($modelValidator, 'attrA');

        $this->assertSame(
            'test-value',
            $modelValidator->attrA,
            'Should trim custom characters from the attribute value.',
        );
    }
}
