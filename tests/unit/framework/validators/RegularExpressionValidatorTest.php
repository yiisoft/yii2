<?php

namespace yiiunit\framework\validators;


use yii\validators\RegularExpressionValidator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

class RegularExpressionValidatorTest extends TestCase
{
	public function testValidateValue()
	{
		$val = new RegularExpressionValidator(array('pattern' => '/^[a-zA-Z0-9](\.)?([^\/]*)$/m'));
		$this->assertTrue($val->validateValue('b.4'));
		$this->assertFalse($val->validateValue('b./'));
		$this->assertFalse($val->validateValue(array('a', 'b')));
		$val->not = true;
		$this->assertFalse($val->validateValue('b.4'));
		$this->assertTrue($val->validateValue('b./'));
		$this->assertFalse($val->validateValue(array('a', 'b')));
	}

	public function testValidateAttribute()
	{
		$val = new RegularExpressionValidator(array('pattern' => '/^[a-zA-Z0-9](\.)?([^\/]*)$/m'));
		$m = FakedValidationModel::createWithAttributes(array('attr_reg1' => 'b.4'));
		$val->validateAttribute($m, 'attr_reg1');
		$this->assertFalse($m->hasErrors('attr_reg1'));
		$m->attr_reg1 = 'b./';
		$val->validateAttribute($m, 'attr_reg1');
		$this->assertTrue($m->hasErrors('attr_reg1'));
	}

	public function testMessageSetOnInit()
	{
		$val = new RegularExpressionValidator(array('pattern' => '/^[a-zA-Z0-9](\.)?([^\/]*)$/m'));
		$this->assertTrue(is_string($val->message));
	}

	public function testInitException()
	{
		$this->setExpectedException('yii\base\InvalidConfigException');
		$val = new RegularExpressionValidator();
		$val->validateValue('abc');
	}

}