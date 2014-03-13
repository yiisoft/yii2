<?php

namespace yiiunit\framework\validators;

use yii\validators\RangeValidator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

class RangeValidatorTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testInitException()
    {
        $this->setExpectedException('yii\base\InvalidConfigException', 'The "range" property must be set.');
        new RangeValidator(['range' => 'not an array']);
    }

    public function testAssureMessageSetOnInit()
    {
        $val = new RangeValidator(['range' => []]);
        $this->assertTrue(is_string($val->message));
    }

    public function testValidateValue()
    {
        $val = new RangeValidator(['range' => range(1, 10, 1)]);
        $this->assertTrue($val->validate(1));
        $this->assertFalse($val->validate(0));
        $this->assertFalse($val->validate(11));
        $this->assertFalse($val->validate(5.5));
        $this->assertTrue($val->validate(10));
        $this->assertTrue($val->validate("10"));
        $this->assertTrue($val->validate("5"));
    }

    public function testValidateValueStrict()
    {
        $val = new RangeValidator(['range' => range(1, 10, 1), 'strict' => true]);
        $this->assertTrue($val->validate(1));
        $this->assertTrue($val->validate(5));
        $this->assertTrue($val->validate(10));
        $this->assertFalse($val->validate("1"));
        $this->assertFalse($val->validate("10"));
        $this->assertFalse($val->validate("5.5"));
    }

    public function testValidateValueNot()
    {
        $val = new RangeValidator(['range' => range(1, 10, 1), 'not' => true]);
        $this->assertFalse($val->validate(1));
        $this->assertTrue($val->validate(0));
        $this->assertTrue($val->validate(11));
        $this->assertTrue($val->validate(5.5));
        $this->assertFalse($val->validate(10));
        $this->assertFalse($val->validate("10"));
        $this->assertFalse($val->validate("5"));
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
        $this->assertTrue(stripos($err[0], 'attr_r2') !== false);
    }
}
