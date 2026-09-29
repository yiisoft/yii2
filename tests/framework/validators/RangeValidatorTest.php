<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\validators;

use ArrayObject;
use yii\validators\RangeValidator;
use yii\validators\Validator;
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
}
