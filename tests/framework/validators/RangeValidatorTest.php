<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\validators;

use ArrayObject;
use yii\base\DynamicModel;
use yii\validators\RangeValidator;
use yii\validators\Validator;
use yiiunit\data\enums\ColorEnum;
use yiiunit\data\enums\PriorityEnum;
use yiiunit\data\enums\StatusEnum;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\framework\validators\stubs\ViewStub;
use yiiunit\TestCase;

/**
 * @group validators
 */
class RangeValidatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockApplication();
    }

    protected function createValidatorInstance(array $config = []): Validator
    {
        return new RangeValidator(array_merge(['range' => [1, 2, 3]], $config));
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->destroyApplication();
    }

    public function testInitException(): void
    {
        $this->expectException('yii\base\InvalidConfigException');
        $this->expectExceptionMessage('The "range" property must be set.');
        new RangeValidator(['range' => 'not an array']);
    }

    public function testAssureMessageSetOnInit(): void
    {
        $val = new RangeValidator(['range' => []]);
        $this->assertIsString($val->message);
    }

    public function testValidateValue(): void
    {
        $val = new RangeValidator(['range' => range(1, 10, 1)]);
        $this->assertTrue($val->validate(1));
        $this->assertFalse($val->validate(0));
        $this->assertFalse($val->validate(11));
        $this->assertFalse($val->validate(5.5));
        $this->assertTrue($val->validate(10));
        $this->assertTrue($val->validate('10'));
        $this->assertTrue($val->validate('5'));
    }

    public function testValidateValueEmpty(): void
    {
        $val = new RangeValidator(['range' => range(10, 20, 1), 'skipOnEmpty' => false]);
        $this->assertFalse($val->validate(null)); //row RangeValidatorTest.php:101
        $this->assertFalse($val->validate('0'));
        $this->assertFalse($val->validate(0));
        $this->assertFalse($val->validate(''));
        $val->allowArray = true;
        $this->assertTrue($val->validate([]));
    }

    public function testValidateArrayValue(): void
    {
        $val = new RangeValidator(['range' => range(1, 10, 1)]);
        $val->allowArray = true;
        $this->assertTrue($val->validate([1, 2, 3, 4, 5]));
        $this->assertTrue($val->validate([6, 7, 8, 9, 10]));
        $this->assertFalse($val->validate([0, 1, 2]));
        $this->assertFalse($val->validate([10, 11, 12]));
        $this->assertTrue($val->validate(['1', '2', '3', 4, 5, 6]));
    }

    public function testValidateValueStrict(): void
    {
        $val = new RangeValidator(['range' => range(1, 10, 1), 'strict' => true]);
        $this->assertTrue($val->validate(1));
        $this->assertTrue($val->validate(5));
        $this->assertTrue($val->validate(10));
        $this->assertFalse($val->validate('1'));
        $this->assertFalse($val->validate('10'));
        $this->assertFalse($val->validate('5.5'));
    }

    public function testValidateArrayValueStrict(): void
    {
        $val = new RangeValidator(['range' => range(1, 10, 1), 'strict' => true]);
        $val->allowArray = true;
        $this->assertFalse($val->validate(['1', '2', '3', '4', '5', '6']));
        $this->assertFalse($val->validate(['1', '2', '3', 4, 5, 6]));
    }

    public function testValidateValueNot(): void
    {
        $val = new RangeValidator(['range' => range(1, 10, 1), 'not' => true]);
        $this->assertFalse($val->validate(1));
        $this->assertTrue($val->validate(0));
        $this->assertTrue($val->validate(11));
        $this->assertTrue($val->validate(5.5));
        $this->assertFalse($val->validate(10));
        $this->assertFalse($val->validate('10'));
        $this->assertFalse($val->validate('5'));
    }

    public function testValidateAttribute(): void
    {
        $val = new RangeValidator(['range' => range(1, 10, 1)]);
        $m = FakedValidationModel::createWithAttributes(['attr_r1' => 5, 'attr_r2' => 999]);
        $val->validateAttribute($m, 'attr_r1');
        $this->assertFalse($m->hasErrors());
        $val->validateAttribute($m, 'attr_r2');
        $this->assertTrue($m->hasErrors('attr_r2'));
        $err = $m->getErrors('attr_r2');
        $this->assertNotFalse(stripos((string) $err[0], 'attr_r2'));
    }

    public function testValidateSubsetArrayable(): void
    {
        // Test in array, values are arrays. IE: ['a'] in [['a'], ['b']]
        $val = new RangeValidator([
            'range' => [['a'], ['b']],
            'allowArray' => false,
        ]);
        $this->assertTrue($val->validate(['a']));

        // Test in array, values are arrays. IE: ['a', 'b'] subset [['a', 'b', 'c']
        $val = new RangeValidator([
            'range' => ['a', 'b', 'c'],
            'allowArray' => true,
        ]);
        $this->assertTrue($val->validate(['a', 'b']));

        // Test in array, values are arrays. IE: ['a', 'b'] subset [['a', 'b', 'c']
        $val = new RangeValidator([
            'range' => ['a', 'b', 'c'],
            'allowArray' => true,
        ]);
        $this->assertTrue($val->validate(new ArrayObject(['a', 'b'])));


        // Test range as ArrayObject.
        $val = new RangeValidator([
            'range' => new ArrayObject(['a', 'b']),
            'allowArray' => false,
        ]);
        $this->assertTrue($val->validate('a'));
        $this->assertFalse($val->validate('c'), 'A value missing from the traversable range must fail.');
    }

    public function testValidateValueWithGeneratorRange(): void
    {
        $val = new RangeValidator([
            'range' => (static function (): \Generator {
                yield 1;
                yield 2;
            })(),
        ]);

        $this->assertTrue(
            $val->validate(2),
            'The first validation must consume the generator.',
        );
        $this->assertTrue(
            $val->validate(1),
            'A second validation must reuse the materialized range instead of the consumed generator.',
        );
        $this->assertFalse(
            $val->validate(3),
            'A value missing from the materialized range must fail.',
        );
        $this->assertSame(
            [1, 2],
            $val->range,
            'The generator must be materialized into an array.',
        );

        $m = FakedValidationModel::createWithAttributes(['attr_range' => 1]);

        $this->assertSame(
            ['1', '2'],
            $val->getClientOptions($m, 'attr_range')['range'],
            'The client options must be built from the materialized range.',
        );
    }

    public function testClientValidateAttributeWithGeneratorRange(): void
    {
        $val = new RangeValidator([
            'range' => (static function (): \Generator {
                yield 1;
                yield 2;
            })(),
        ]);

        $m = FakedValidationModel::createWithAttributes(['attr_range' => 1]);

        $this->assertSame(
            'yii.validation.range(value, messages, {"range":["1","2"],"not":false,"message":"attr_range is invalid.","skipOnEmpty":1});',
            $val->clientValidateAttribute($m, 'attr_range', new ViewStub()),
            'The client options must consume the generator.',
        );

        $val->validateAttribute($m, 'attr_range');

        $this->assertFalse(
            $m->hasErrors('attr_range'),
            'A validation after the client options must reuse the materialized range instead of the consumed generator.',
        );
    }

    public function testValidateAttributeWithClosureRangeReturningGenerator(): void
    {
        $val = new RangeValidator([
            'range' => static function ($model, $attribute): \Generator {
                yield 1;
                yield 2;
            },
        ]);

        $m = FakedValidationModel::createWithAttributes(['attr_range' => 2]);

        $val->validateAttribute($m, 'attr_range');

        $this->assertFalse(
            $m->hasErrors('attr_range'),
            'The generator returned by the closure must accept one of its members.',
        );

        $m->attr_range = 1;

        $val->validateAttribute($m, 'attr_range');

        $this->assertFalse(
            $m->hasErrors('attr_range'),
            'A second validation must reuse the materialized range instead of the consumed generator.',
        );

        $m->attr_range = 3;

        $val->validateAttribute($m, 'attr_range');

        $this->assertTrue(
            $m->hasErrors('attr_range'),
            'The materialized range must reject an outsider.',
        );
    }

    public function testValidateValueWithConsumedGeneratorRange(): void
    {
        $range = (static function (): \Generator {
            yield 1;
            yield 2;
        })();

        iterator_to_array($range, false);

        $val = new RangeValidator(['range' => $range]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Cannot traverse an already closed generator');

        $val->validate(1);
    }

    public function testValidateAttributeWithClosureRange(): void
    {
        $val = new RangeValidator([
            'range' => function ($model, $attribute) {
                return [1, 2];
            },
        ]);

        $m = FakedValidationModel::createWithAttributes(['attr_range' => 1]);

        $val->validateAttribute($m, 'attr_range');

        $this->assertFalse(
            $m->hasErrors('attr_range'),
            'The computed range must accept one of its members.',
        );

        $m->attr_range = 3;

        $val->validateAttribute($m, 'attr_range');

        $this->assertTrue(
            $m->hasErrors('attr_range'),
            'The computed range must reject an outsider.',
        );
    }

    /**
     * Legacy client-side contract; not applicable to 22.0.
     */
    public function testClientValidateAttribute(): void
    {
        $val = new RangeValidator(['range' => [1, 2], 'allowArray' => true, 'not' => true]);

        $m = FakedValidationModel::createWithAttributes(['attr_range' => 1]);

        $this->assertSame(
            'yii.validation.range(value, messages, {"range":["1","2"],"not":true,"message":"attr_range is invalid.","skipOnEmpty":1,"allowArray":1});',
            $val->clientValidateAttribute($m, 'attr_range', new ViewStub()),
            'Client script must pin the whole option set.',
        );

        $val->range = function ($model, $attribute) {
            return [3, 4];
        };

        $this->assertSame(
            'yii.validation.range(value, messages, {"range":["3","4"],"not":true,"message":"attr_range is invalid.","skipOnEmpty":1,"allowArray":1});',
            $val->clientValidateAttribute($m, 'attr_range', new ViewStub()),
            'The computed range must reach the client options.',
        );
    }

    /**
     * @requires PHP >= 8.1
     */
    public function testValidateValueWithBackedEnum(): void
    {
        $val = new RangeValidator(['range' => StatusEnum::cases()]);

        $this->assertTrue(
            $val->validate(StatusEnum::ACTIVE),
            'A case of the backed enum must be accepted.',
        );
        $this->assertTrue(
            $val->validate(StatusEnum::INACTIVE),
            'A case of the backed enum must be accepted.',
        );
        $this->assertTrue(
            $val->validate(1),
            'The backing value of a case must be accepted.',
        );
        $this->assertTrue(
            $val->validate('1'),
            'The backing value submitted as a string must be accepted.',
        );
        $this->assertFalse(
            $val->validate(5),
            'A value not in the range must be rejected.',
        );
        $this->assertFalse(
            $val->validate('5'),
            'A value not in the range must be rejected.',
        );
        $this->assertFalse(
            $val->validate('ACTIVE'),
            'The case name of a backed enum is not its scalar representation.',
        );
        $this->assertFalse(
            $val->validate(PriorityEnum::LOW),
            'A case of another enum must not be accepted, even if it has the same backing value.',
        );
    }

    /**
     * @requires PHP >= 8.1
     */
    public function testValidateValueWithUnitEnum(): void
    {
        $val = new RangeValidator(['range' => ColorEnum::cases()]);

        $this->assertTrue(
            $val->validate(ColorEnum::RED),
            'The case must be accepted.',
        );
        $this->assertTrue(
            $val->validate('RED'),
            'The case name must be accepted.',
        );
        $this->assertFalse(
            $val->validate('PURPLE'),
            'The case name must not be accepted.',
        );
        $this->assertFalse(
            $val->validate(0),
            'The value must not be accepted.',
        );
        $this->assertFalse(
            $val->validate(StatusEnum::ACTIVE),
            'The value must not be accepted.',
        );
    }

    /**
     * @requires PHP >= 8.1
     */
    public function testValidateValueStrictWithBackedEnum(): void
    {
        $val = new RangeValidator(['range' => StatusEnum::cases(), 'strict' => true]);

        $this->assertTrue(
            $val->validate(StatusEnum::ACTIVE),
            'A case of the backed enum must be accepted.',
        );
        $this->assertTrue(
            $val->validate(1),
            'The backing value of a case must be accepted.',
        );
        $this->assertFalse(
            $val->validate('1'),
            'Strict comparison must keep rejecting a string for an integer backing value.',
        );
    }

    /**
     * @requires PHP >= 8.1
     */
    public function testValidateArrayValueWithEnum(): void
    {
        $val = new RangeValidator(['range' => StatusEnum::cases(), 'allowArray' => true]);

        $this->assertTrue(
            $val->validate([StatusEnum::ACTIVE, 2, '0']),
            'All values in the array must be in the range.',
        );
        $this->assertFalse(
            $val->validate([StatusEnum::ACTIVE, 5]),
            'If any value in the array is not in the range, validation must fail.',
        );
        $this->assertFalse(
            $val->validate([PriorityEnum::HIGH]),
            'If any value in the array is not in the range, validation must fail.',
        );
    }

    /**
     * @requires PHP >= 8.1
     */
    public function testValidateValueNotWithEnum(): void
    {
        $val = new RangeValidator(['range' => StatusEnum::cases(), 'not' => true]);

        $this->assertFalse(
            $val->validate(StatusEnum::ACTIVE),
            'The value must not be in the range.',
        );
        $this->assertFalse(
            $val->validate(1),
            'The value must not be in the range.',
        );
        $this->assertTrue(
            $val->validate(5),
            'The value must not be in the range.',
        );
        $this->assertTrue(
            $val->validate(PriorityEnum::LOW),
            'The value must not be in the range.',
        );
    }

    /**
     * @requires PHP >= 8.1
     */
    public function testValidateAttributeWithEnumRange(): void
    {
        $val = new RangeValidator(['range' => StatusEnum::cases()]);

        $m = FakedValidationModel::createWithAttributes(['attr_r1' => '2', 'attr_r2' => '9']);

        $val->validateAttribute($m, 'attr_r1');

        $this->assertFalse(
            $m->hasErrors('attr_r1'),
            'A submitted backing value must pass server-side validation.',
        );

        $val->validateAttribute($m, 'attr_r2');

        $this->assertTrue(
            $m->hasErrors('attr_r2'),
            'A submitted backing value must fail server-side validation.',
        );
    }

    /**
     * @requires PHP >= 8.1
     */
    public function testClientValidateAttributeWithEnumRange(): void
    {
        $m = FakedValidationModel::createWithAttributes(['attr_range' => StatusEnum::ACTIVE]);

        $val = new RangeValidator(['range' => StatusEnum::cases()]);

        $this->assertSame(
            'yii.validation.range(value, messages, {"range":["1","2","0"],"not":false,"message":"attr_range is invalid.","skipOnEmpty":1});',
            $val->clientValidateAttribute($m, 'attr_range', new ViewStub()),
            'The client options must contain the backing values of the cases.',
        );

        $val = new RangeValidator(['range' => ColorEnum::cases()]);

        $this->assertSame(
            'yii.validation.range(value, messages, {"range":["BLUE","GREEN","RED"],"not":false,"message":"attr_range is invalid.","skipOnEmpty":1});',
            $val->clientValidateAttribute($m, 'attr_range', new ViewStub()),
            'The client options must contain the names of the cases of a pure enum.',
        );
    }

    /**
     * @requires PHP >= 8.1
     */
    public function testValidateDataWithEnumRange(): void
    {
        $rules = [['status', 'in', 'range' => StatusEnum::cases()]];

        $model = DynamicModel::validateData(['status' => '2'], $rules);

        $this->assertFalse(
            $model->hasErrors('status'),
            'The backing value submitted by a form must pass the same rule that accepts the enum case.',
        );

        $model = DynamicModel::validateData(['status' => StatusEnum::DELETED], $rules);

        $this->assertFalse(
            $model->hasErrors('status'),
            'An enum case assigned to the attribute must pass the rule.',
        );

        $model = DynamicModel::validateData(['status' => '9'], $rules);

        $this->assertTrue(
            $model->hasErrors('status'),
            'A submitted value outside the range must fail the rule.',
        );
    }
}
