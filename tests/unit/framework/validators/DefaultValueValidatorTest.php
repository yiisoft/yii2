<?php
namespace yiiunit\framework\validators;
use yii\validators\DefaultValueValidator;
use yiiunit\TestCase;

/**
 * DefaultValueValidatorTest
 */
class DefaultValueValidatorTest extends TestCase
{
	public function testValidateAttribute()
	{
		$val = new DefaultValueValidator;
		$val->value = 'test_value';
		$obj = new \stdclass;
		$obj->attrA = 'attrA';
		$obj->attrB = null;
		$obj->attrC = '';
		// original values to chek which attritubes where modified
		$objB = clone $obj;
		$val->validateAttribute($obj, 'attrB');
		$this->assertEquals($val->value, $obj->attrB);
		$this->assertEquals($objB->attrA, $obj->attrA);
		$val->value = 'new_test_value';
		$obj = clone $objB; // get clean object
		$val->validateAttribute($obj, 'attrC');
		$this->assertEquals('new_test_value', $obj->attrC);
		$this->assertEquals($objB->attrA, $obj->attrA);
		$val->validateAttribute($obj, 'attrA');
		$this->assertEquals($objB->attrA, $obj->attrA);
	}
}
