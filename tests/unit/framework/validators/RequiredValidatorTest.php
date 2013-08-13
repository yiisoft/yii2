<?php
namespace yiiunit\framework\validators;


use yii\validators\RequiredValidator;
use yiiunit\TestCase;

class RequiredValidatorTest extends TestCase
{
	public function testValidateValueWithDefaults()
	{
		$val = new RequiredValidator();
		$this->assertFalse($val->validateValue(null));
		$this->assertFalse($val->validateValue(array()));
		$this->assertTrue($val->validateValue('not empty'));
		$this->assertTrue($val->validateValue(array('with', 'elements')));
	}

	public function testValidateValueWithValue()
	{
		$val = new RequiredValidator(array('requiredValue' => 55));
		$this->assertTrue($val->validateValue(55));
		$this->assertTrue($val->validateValue("55"));
		$this->assertTrue($val->validateValue("0x37"));
		$this->assertFalse($val->validateValue("should fail"));
		$this->assertTrue($val->validateValue(true));
		$val->strict = true;
		$this->assertTrue($val->validateValue(55));
		$this->assertFalse($val->validateValue("55"));
		$this->assertFalse($val->validateValue("0x37"));
		$this->assertFalse($val->validateValue("should fail"));
		$this->assertFalse($val->validateValue(true));
	}
}