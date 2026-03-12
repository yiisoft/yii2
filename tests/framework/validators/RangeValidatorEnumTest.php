<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\validators;

use yii\validators\RangeValidator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\framework\validators\stubs\IntStatus;
use yiiunit\framework\validators\stubs\StringStatus;
use yiiunit\framework\validators\stubs\Suit;
use yiiunit\TestCase;

/**
 * @group validators
 * @requires PHP >= 8.1
 */
class RangeValidatorEnumTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once __DIR__ . '/stubs/EnumStubs.php';
        $this->destroyApplication();
    }

    public function testEnumWithStringBackedValues(): void
    {
        $val = new RangeValidator(['enum' => StringStatus::class]);
        $this->assertTrue($val->validate('active'));
        $this->assertTrue($val->validate('inactive'));
        $this->assertFalse($val->validate('pending'));
        $this->assertFalse($val->validate(''));
    }

    public function testEnumWithIntBackedValues(): void
    {
        $val = new RangeValidator(['enum' => IntStatus::class]);
        $this->assertTrue($val->validate(1));
        $this->assertTrue($val->validate(0));
        $this->assertFalse($val->validate(2));
    }

    public function testEnumWithNameTarget(): void
    {
        $val = new RangeValidator(['enum' => StringStatus::class, 'target' => 'name']);
        $this->assertTrue($val->validate('Active'));
        $this->assertTrue($val->validate('Inactive'));
        $this->assertFalse($val->validate('active'));
    }

    public function testEnumUnitEnumWithNameTarget(): void
    {
        $val = new RangeValidator(['enum' => Suit::class, 'target' => 'name']);
        $this->assertTrue($val->validate('Hearts'));
        $this->assertTrue($val->validate('Spades'));
        $this->assertFalse($val->validate('hearts'));
        $this->assertFalse($val->validate(''));
    }

    public function testEnumUnitEnumWithValueTargetThrows(): void
    {
        $this->expectException('yii\base\InvalidConfigException');
        $this->expectExceptionMessage('The "value" target requires a backed enum');
        new RangeValidator(['enum' => Suit::class]);
    }

    public function testEnumInvalidClassThrows(): void
    {
        $this->expectException('yii\base\InvalidConfigException');
        $this->expectExceptionMessage('The "enum" property must be a valid enum class');
        new RangeValidator(['enum' => \stdClass::class]);
    }

    public function testEnumWithNotProperty(): void
    {
        $val = new RangeValidator(['enum' => StringStatus::class, 'not' => true]);
        $this->assertFalse($val->validate('active'));
        $this->assertTrue($val->validate('pending'));
    }

    public function testEnumWithStrictProperty(): void
    {
        $val = new RangeValidator(['enum' => IntStatus::class, 'strict' => true]);
        $this->assertTrue($val->validate(1));
        $this->assertFalse($val->validate('1'));
    }

    public function testEnumValidateAttribute(): void
    {
        $val = new RangeValidator(['enum' => StringStatus::class]);
        $m = FakedValidationModel::createWithAttributes(['attr_status' => 'active']);
        $val->validateAttribute($m, 'attr_status');
        $this->assertFalse($m->hasErrors('attr_status'));

        $m = FakedValidationModel::createWithAttributes(['attr_status' => 'bogus']);
        $val->validateAttribute($m, 'attr_status');
        $this->assertTrue($m->hasErrors('attr_status'));
    }

    public function testGetClientOptionsWithEnumCasesInRange(): void
    {
        $this->mockWebApplication();
        $val = new RangeValidator(['range' => StringStatus::cases()]);
        $m = FakedValidationModel::createWithAttributes(['attr_status' => 'active']);
        $options = $val->getClientOptions($m, 'attr_status');
        $this->assertSame(['active', 'inactive'], $options['range']);
    }

    public function testGetClientOptionsWithIntEnumCasesInRange(): void
    {
        $this->mockWebApplication();
        $val = new RangeValidator(['range' => IntStatus::cases()]);
        $m = FakedValidationModel::createWithAttributes(['attr_status' => 1]);
        $options = $val->getClientOptions($m, 'attr_status');
        $this->assertSame(['1', '0'], $options['range']);
    }

    public function testGetClientOptionsWithUnitEnumCasesInRange(): void
    {
        $this->mockWebApplication();
        $val = new RangeValidator(['range' => Suit::cases()]);
        $m = FakedValidationModel::createWithAttributes(['attr_suit' => 'Hearts']);
        $options = $val->getClientOptions($m, 'attr_suit');
        $this->assertSame(['Hearts', 'Diamonds', 'Clubs', 'Spades'], $options['range']);
    }

    public function testGetClientOptionsWithEnumProperty(): void
    {
        $this->mockWebApplication();
        $val = new RangeValidator(['enum' => StringStatus::class]);
        $m = FakedValidationModel::createWithAttributes(['attr_status' => 'active']);
        $options = $val->getClientOptions($m, 'attr_status');
        $this->assertSame(['active', 'inactive'], $options['range']);
    }
}
