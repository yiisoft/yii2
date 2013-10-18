<?php
namespace yiiunit\framework\validators;

use yiiunit\data\validators\models\FakedValidationModel;
use yii\validators\BooleanValidator;
use yiiunit\TestCase;

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
		$this->assertFalse($val->validateValue(null));
		$this->assertFalse($val->validateValue([]));
		$val->strict = true;
		$this->assertTrue($val->validateValue('0'));
		$this->assertTrue($val->validateValue('1'));
		$this->assertFalse($val->validateValue(true));
		$this->assertFalse($val->validateValue(false));
		$val->trueValue = true;
		$val->falseValue = false;
		$this->assertFalse($val->validateValue('0'));
		$this->assertFalse($val->validateValue([]));
		$this->assertTrue($val->validateValue(true));
		$this->assertTrue($val->validateValue(false));
	}

	public function testValidateAttributeAndError()
	{
		$obj = new FakedValidationModel;
		$obj->attrA = true;
		$obj->attrB = '1';
		$obj->attrC = '0';
		$obj->attrD = [];
		$val = new BooleanValidator;
		$val->validateAttribute($obj, 'attrA');
		$this->assertFalse($obj->hasErrors('attrA'));
		$val->validateAttribute($obj, 'attrC');
		$this->assertFalse($obj->hasErrors('attrC'));
		$val->strict = true;
		$val->validateAttribute($obj, 'attrB');
		$this->assertFalse($obj->hasErrors('attrB'));
		$val->validateAttribute($obj, 'attrD');
		$this->assertTrue($obj->hasErrors('attrD'));
	}
}
