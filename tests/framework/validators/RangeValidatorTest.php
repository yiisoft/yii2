<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\validators;

use yii\validators\RangeValidator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

/**
 * @group validators
 */
class RangeValidatorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // destroy application, Validator must work without Yii::$app
        $this->destroyApplication();
    }

    public function testInitException()
    {
        $this->expectException('yii\base\InvalidConfigException');
        $this->expectExceptionMessage('The "range" property must be set.');
        new RangeValidator(['range' => 'not an array']);
    }

    public function testAssureMessageSetOnInit()
    {
        $val = new RangeValidator(['range' => []]);
        $this->assertIsString($val->message);
    }

    public function testValidateValue()
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

    public function testValidateValueEmpty()
    {
        $val = new RangeValidator(['range' => range(10, 20, 1), 'skipOnEmpty' => false]);
        $this->assertFalse($val->validate(null)); //row RangeValidatorTest.php:101
        $this->assertFalse($val->validate('0'));
        $this->assertFalse($val->validate(0));
        $this->assertFalse($val->validate(''));
        $val->allowArray = true;
        $this->assertTrue($val->validate([]));
    }

    public function testValidateArrayValue()
    {
        $val = new RangeValidator(['range' => range(1, 10, 1)]);
        $val->allowArray = true;
        $this->assertTrue($val->validate([1, 2, 3, 4, 5]));
        $this->assertTrue($val->validate([6, 7, 8, 9, 10]));
        $this->assertFalse($val->validate([0, 1, 2]));
        $this->assertFalse($val->validate([10, 11, 12]));
        $this->assertTrue($val->validate(['1', '2', '3', 4, 5, 6]));
    }

    public function testValidateValueStrict()
    {
        $val = new RangeValidator(['range' => range(1, 10, 1), 'strict' => true]);
        $this->assertTrue($val->validate(1));
        $this->assertTrue($val->validate(5));
        $this->assertTrue($val->validate(10));
        $this->assertFalse($val->validate('1'));
        $this->assertFalse($val->validate('10'));
        $this->assertFalse($val->validate('5.5'));
    }

    public function testValidateArrayValueStrict()
    {
        $val = new RangeValidator(['range' => range(1, 10, 1), 'strict' => true]);
        $val->allowArray = true;
        $this->assertFalse($val->validate(['1', '2', '3', '4', '5', '6']));
        $this->assertFalse($val->validate(['1', '2', '3', 4, 5, 6]));
    }

    public function testValidateValueNot()
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

    public function testValidateAttribute()
    {
        $val = new RangeValidator(['range' => range(1, 10, 1)]);
        $m = FakedValidationModel::createWithAttributes(['attr_r1' => 5, 'attr_r2' => 999]);
        $val->validateAttribute($m, 'attr_r1');
        $this->assertFalse($m->hasErrors());
        $val->validateAttribute($m, 'attr_r2');
        $this->assertTrue($m->hasErrors('attr_r2'));
        $err = $m->getErrors('attr_r2');
        $this->assertNotFalse(stripos($err[0], 'attr_r2'));
    }

    public function testValidateSubsetArrayable()
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
        $this->assertTrue($val->validate(new \ArrayObject(['a', 'b'])));


        // Test range as ArrayObject.
        $val = new RangeValidator([
            'range' => new \ArrayObject(['a', 'b']),
            'allowArray' => false,
        ]);
        $this->assertTrue($val->validate('a'));
    }
}
