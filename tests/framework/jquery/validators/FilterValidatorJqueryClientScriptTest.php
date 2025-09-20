<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\jquery\validators;

use Yii;
use yii\validators\FilterValidator;
use yii\web\View;
use yiiunit\data\validators\models\FakedValidationModel;

/**
 * @group jquery
 */
final class FilterValidatorJqueryClientScriptTest extends \yiiunit\TestCase
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
        $validator = new FilterValidator(['filter' => 'trim']);

        $modelValidator->attrA = '  test value  ';

        $this->assertSame(
            'value = yii.validation.trim($form, attribute, [], value);',
            $validator->clientValidateAttribute($modelValidator, 'attrB', new View()),
            "'clientValidateAttribute()' method should return correct validation script.",
        );
        $this->assertSame(
            [],
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

    public function testClientValidateAttributeWithArraySkip(): void
    {
        $modelValidator = new FakedValidationModel();
        $validator = new FilterValidator([
            'filter' => 'trim',
            'skipOnArray' => true,
        ]);

        $modelValidator->attrA = ['  test  ', '  value  '];

        $this->assertSame(
            'value = yii.validation.trim($form, attribute, [], value);',
            $validator->clientValidateAttribute($modelValidator, 'attrB', new View()),
            "'clientValidateAttribute()' method should return correct validation script.",
        );
        $this->assertSame(
            [],
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
            'Should skip array values when skipOnArray is true.',
        );
    }

    public function testClientValidateAttributeWithNonTrimFilter(): void
    {
        $modelValidator = new FakedValidationModel();
        $validator = new FilterValidator(['filter' => 'strtoupper']);

        $modelValidator->attrA = 'test value';

        $this->assertSame(
            '',
            $validator->clientValidateAttribute($modelValidator, 'attrB', new View()),
            "'clientValidateAttribute()' method should return correct validation script.",
        );
        $this->assertSame(
            [],
            $validator->getClientOptions($modelValidator, 'attrA'),
            "'getClientOptions()' method should return correct options array.",
        );

        $validator->validateAttribute($modelValidator, 'attrA');

        $this->assertSame(
            'TEST VALUE',
            $modelValidator->attrA,
            'Should apply the strtoupper filter.',
        );
    }

    public function testClientValidateAttributeWithTrimFilterAndSkipOnEmpty(): void
    {
        $modelValidator = new FakedValidationModel();
        $validator = new FilterValidator(
            [
                'filter' => 'trim',
                'skipOnEmpty' => true,
            ],
        );

        $modelValidator->attrA = '  test value  ';

        $this->assertSame(
            'value = yii.validation.trim($form, attribute, {"skipOnEmpty":1}, value);',
            $validator->clientValidateAttribute($modelValidator, 'attrB', new View()),
            "'clientValidateAttribute()' method should return correct validation script.",
        );
        $this->assertSame(
            [
                'skipOnEmpty' => 1,
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

    public function testClientValidateAttributeWithUseJqueryFalse(): void
    {
        Yii::$app->useJquery = false;

        $modelValidator = new FakedValidationModel();
        $validator = new FilterValidator(['filter' => 'trim']);

        $modelValidator->attrA = '  test value  ';

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
            'test value',
            $modelValidator->attrA,
            'Should trim the attribute value.',
        );
    }
}
