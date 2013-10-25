<?php

namespace yiiunit\framework\validators;


use Yii;
use yii\base\Exception;
use yii\validators\ExistValidator;
use yiiunit\data\ar\ActiveRecord;
use yiiunit\data\validators\models\ValidatorTestMainModel;
use yiiunit\data\validators\models\ValidatorTestRefModel;
use yiiunit\framework\db\DatabaseTestCase;

class ExistValidatorTest extends DatabaseTestCase
{
	protected $driverName = 'mysql';

	public function setUp()
	{
		parent::setUp();
		$this->mockApplication();
		ActiveRecord::$db = $this->getConnection();
	}

	public function testValidateValueExpectedException()
	{
		try {
			$val = new ExistValidator();
			$result = $val->validateValue('ref');
			$this->fail('Exception should have been thrown at this time');
		} catch (Exception $e) {
			$this->assertInstanceOf('yii\base\InvalidConfigException', $e);
			$this->assertEquals('The "className" property must be set.', $e->getMessage());
		}
		// combine to save the time creating a new db-fixture set (likely ~5 sec)
		try {
			$val = new ExistValidator(['className' => ValidatorTestMainModel::className()]);
			$val->validateValue('ref');
			$this->fail('Exception should have been thrown at this time');
		} catch (Exception $e) {
			$this->assertInstanceOf('yii\base\InvalidConfigException', $e);
			$this->assertEquals('The "attributeName" property must be set.', $e->getMessage());
		}
	}

	public function testValidateValue()
	{
		$val = new ExistValidator(['className' => ValidatorTestRefModel::className(), 'attributeName' => 'id']);
		$this->assertTrue($val->validateValue(2));
		$this->assertTrue($val->validateValue(5));
		$this->assertFalse($val->validateValue(99));
		$this->assertFalse($val->validateValue(['1']));
	}

	public function testValidateAttribute()
	{
		// existing value on different table
		$val = new ExistValidator(['className' => ValidatorTestMainModel::className(), 'attributeName' => 'id']);
		$m = ValidatorTestRefModel::find(['id' => 1]);
		$val->validateAttribute($m, 'ref');
		$this->assertFalse($m->hasErrors());
		// non-existing value on different table
		$val = new ExistValidator(['className' => ValidatorTestMainModel::className(), 'attributeName' => 'id']);
		$m = ValidatorTestRefModel::find(['id' => 6]);
		$val->validateAttribute($m, 'ref');
		$this->assertTrue($m->hasErrors('ref'));
		// existing value on same table
		$val = new ExistValidator(['attributeName' => 'ref']);
		$m = ValidatorTestRefModel::find(['id' => 2]);
		$val->validateAttribute($m, 'test_val');
		$this->assertFalse($m->hasErrors());
		// non-existing value on same table
		$val = new ExistValidator(['attributeName' => 'ref']);
		$m = ValidatorTestRefModel::find(['id' => 5]);
		$val->validateAttribute($m, 'test_val_fail');
		$this->assertTrue($m->hasErrors('test_val_fail'));
		// check for given value (true)
		$val = new ExistValidator();
		$m = ValidatorTestRefModel::find(['id' => 3]);
		$val->validateAttribute($m, 'ref');
		$this->assertFalse($m->hasErrors());
		// check for given defaults (false)
		$val = new ExistValidator();
		$m = ValidatorTestRefModel::find(['id' => 4]);
		$m->a_field = 'some new value';
		$val->validateAttribute($m, 'a_field');
		$this->assertTrue($m->hasErrors('a_field'));
		// check array
		$val = new ExistValidator(['attributeName' => 'ref']);
		$m = ValidatorTestRefModel::find(['id' => 2]);
		$m->test_val = [1,2,3];
		$val->validateAttribute($m, 'test_val');
		$this->assertTrue($m->hasErrors('test_val'));
	}
}