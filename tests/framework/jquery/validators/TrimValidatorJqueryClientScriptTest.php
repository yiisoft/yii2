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
use yii\validators\TrimValidator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

/**
 * Unit tests for {@see TrimValidator} client validation script.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('jquery')]
#[Group('validators')]
final class TrimValidatorJqueryClientScriptTest extends TestCase
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

        $modelValidator->attrA = '  test value  ';

        $validator = new TrimValidator();

        self::assertSame(
            <<<JS
            value = yii.validation.trim(\$form, attribute, {"skipOnArray":false,"skipOnEmpty":false,"chars":false}, value);
            JS,
            $validator->clientValidateAttribute($modelValidator, 'attrA', Yii::$app->view),
            'Should return correct validation script.',
        );
        self::assertSame(
            [
                'skipOnArray' => false,
                'skipOnEmpty' => false,
                'chars' => false,
            ],
            $validator->getClientOptions($modelValidator, 'attrA'),
            'Should return correct options array.',
        );

        $validator->validateAttribute($modelValidator, 'attrA');

        self::assertSame(
            'test value',
            $modelValidator->attrA,
            'Should trim the attribute value.',
        );
    }

    public function testClientValidateAttributeWithSkipOnArray(): void
    {
        $modelValidator = new FakedValidationModel();

        $modelValidator->attrA = ['  test  ', '  value  '];

        $validator = new TrimValidator(['skipOnArray' => true]);

        self::assertEmpty(
            $validator->clientValidateAttribute($modelValidator, 'attrA', Yii::$app->view),
            "Should return empty string when 'skipOnArray' is 'true' and value is array.",
        );
        self::assertSame(
            [
                'skipOnArray' => true,
                'skipOnEmpty' => false,
                'chars' => false,
            ],
            $validator->getClientOptions($modelValidator, 'attrA'),
            'Should return correct options array.',
        );

        $validator->validateAttribute($modelValidator, 'attrA');

        self::assertSame(
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

        $modelValidator->attrA = '//test-value--';

        $validator = new TrimValidator(['chars' => '/-']);

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

        $validator->validateAttribute($modelValidator, 'attrA');

        self::assertSame(
            'test-value',
            $modelValidator->attrA,
            'Should trim custom characters from the attribute value.',
        );
    }
}
