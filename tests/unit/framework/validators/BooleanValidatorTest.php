<?php
namespace yiiunit\framework\validators;
use yiiunit\framework\validators\FakedValidationModel;
use yii\validators\BooleanValidator;
use yiiunit\TestCase;

require_once __DIR__ . '/FakedValidationModel.php';
/**
 * BooleanValidatorTest
 */
class BooleanValidatorTest extends TestCase
{
	public function testValidateValue()
	{
		$val = new BooleanValidator;
		$this->assertTrue($val->validateValue(true));
		$this->assertTrue($val->validateValue(false));
		$this->assertTrue($val->validateValue('0'));
		$this->assertTrue($val->validateValue('1'));
		$this->assertTrue($val->validateValue(null));
		$this->assertTrue($val->validateValue(array()));
		$val->strict = true;
		$this->assertTrue($val->validateValue('0'));
		$this->assertTrue($val->validateValue('1'));
		$this->assertFalse($val->validateValue(true));
		$this->assertFalse($val->validateValue(false));
		$val->trueValue = true;
		$val->falseValue = false;
		$this->assertFalse($val->validateValue('0'));
		$this->assertFalse($val->validateValue(array()));
		$this->assertTrue($val->validateValue(true));
		$this->assertTrue($val->validateValue(false));
	}
	
	public function testValidateAttributeAndError()
	{
		$obj = new FakedValidationModel;
		$obj->attrA = true;
		$obj->attrB = '1';
		$obj->attrC = '0';
		$obj->attrD = array();
		$val = new BooleanValidator;
		$val->validateAttribute($obj, 'attrA');
		$this->assertFalse(isset($obj->errors['attrA']));
		$val->validateAttribute($obj, 'attrC');
		$this->assertFalse(isset($obj->errors['attrC']));
		$val->strict = true;
		$val->validateAttribute($obj, 'attrB');
		$this->assertFalse(isset($obj->errors['attrB']));
		$val->validateAttribute($obj, 'attrD');
		$this->assertTrue(isset($obj->errors['attrD']));
	}
}
