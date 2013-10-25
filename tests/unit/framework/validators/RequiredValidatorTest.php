<?php
namespace yiiunit\framework\validators;


use yii\validators\RequiredValidator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

class RequiredValidatorTest extends TestCase
{
	protected function setUp()
	{
		parent::setUp();
		$this->mockApplication();
	}

	public function testValidateValueWithDefaults()
	{
		$val = new RequiredValidator();
		$this->assertFalse($val->validateValue(null));
		$this->assertFalse($val->validateValue([]));
		$this->assertTrue($val->validateValue('not empty'));
		$this->assertTrue($val->validateValue(['with', 'elements']));
	}

	public function testValidateValueWithValue()
	{
		$val = new RequiredValidator(['requiredValue' => 55]);
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

	public function testValidateAttribute()
	{
		// empty req-value
		$val = new RequiredValidator();
		$m = FakedValidationModel::createWithAttributes(['attr_val' => null]);
		$val->validateAttribute($m, 'attr_val');
		$this->assertTrue($m->hasErrors('attr_val'));
		$this->assertTrue(stripos(current($m->getErrors('attr_val')), 'blank') !== false);
		$val = new RequiredValidator(['requiredValue' => 55]);
		$m = FakedValidationModel::createWithAttributes(['attr_val' => 56]);
		$val->validateAttribute($m, 'attr_val');
		$this->assertTrue($m->hasErrors('attr_val'));
		$this->assertTrue(stripos(current($m->getErrors('attr_val')), 'must be') !== false);
		$val = new RequiredValidator(['requiredValue' => 55]);
		$m = FakedValidationModel::createWithAttributes(['attr_val' => 55]);
		$val->validateAttribute($m, 'attr_val');
		$this->assertFalse($m->hasErrors('attr_val'));
	}
}