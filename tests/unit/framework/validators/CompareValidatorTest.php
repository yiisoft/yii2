<?php
namespace yiiunit\framework\validators;

use yii\base\InvalidConfigException;
use yii\validators\CompareValidator;
use yiiunit\TestCase;

require_once __DIR__ . '/FakedValidationModel.php';


class CompareValidatorTest extends TestCase
{

	public function testValidateValueException()
	{
		$this->setExpectedException('yii\base\InvalidConfigException');
		$val = new CompareValidator;
		$val->validateValue('val');
	}

	public function testValidateValue()
	{
		$value = 18449;
		// default config
		$val = new CompareValidator(array('compareValue' => $value));
		$this->assertTrue($val->validateValue($value));
		$this->assertTrue($val->validateValue((string)$value));
		$this->assertFalse($val->validateValue($value + 1));
		foreach ($this->getOperationTestData($value) as $op => $tests) {
			$val = new CompareValidator(array('compareValue' => $value));
			$val->operator = $op;
			foreach ($tests as $test) {
				$this->assertEquals($test[1], $val->validateValue($test[0]));
			}
		}
	}

	protected function getOperationTestData($value)
	{
		return array(
			'===' => array(
				array($value, true),
				array((string)$value, false),
				array((float)$value, false),
				array($value + 1, false),
			),
			'!=' => array(
				array($value, false),
				array((string)$value, false),
				array((float)$value, false),
				array($value + 0.00001, true),
				array(false, true),
			),
			'!==' => array(
				array($value, false),
				array((string)$value, true),
				array((float)$value, true),
				array(false, true),
			),
			'>' => array(
				array($value, false),
				array($value + 1, true),
				array($value - 1, false),
			),
			'>=' => array(
				array($value, true),
				array($value + 1, true),
				array($value - 1, false),
			),
			'<' => array(
				array($value, false),
				array($value + 1, false),
				array($value - 1, true),
			),
			'<=' => array(
				array($value, true),
				array($value + 1, false),
				array($value - 1, true),
			),

		);
	}

	public function testValidateAttribute()
	{
		// invalid-array
		$val = new CompareValidator;
		$model = new FakedValidationModel;
		$model->attr = array('test_val');
		$val->validateAttribute($model, 'attr');
		$this->assertTrue($model->hasErrors('attr'));
		$val = new CompareValidator(array('compareValue' => 'test-string'));
		$model = new FakedValidationModel;
		$model->attr_test = 'test-string';
		$val->validateAttribute($model, 'attr_test');
		$this->assertFalse($model->hasErrors('attr_test'));
		$val = new CompareValidator(array('compareAttribute' => 'attr_test_val'));
		$model = new FakedValidationModel;
		$model->attr_test = 'test-string';
		$model->attr_test_val = 'test-string';
		$val->validateAttribute($model, 'attr_test');
		$this->assertFalse($model->hasErrors('attr_test'));
		$this->assertFalse($model->hasErrors('attr_test_val'));
		$val = new CompareValidator(array('compareAttribute' => 'attr_test_val'));
		$model = new FakedValidationModel;
		$model->attr_test = 'test-string';
		$model->attr_test_val = 'test-string-false';
		$val->validateAttribute($model, 'attr_test');
		$this->assertTrue($model->hasErrors('attr_test'));
		$this->assertFalse($model->hasErrors('attr_test_val'));
		// assume: _repeat
		$val = new CompareValidator;
		$model = new FakedValidationModel;
		$model->attr_test = 'test-string';
		$model->attr_test_repeat = 'test-string';
		$val->validateAttribute($model, 'attr_test');
		$this->assertFalse($model->hasErrors('attr_test'));
		$this->assertFalse($model->hasErrors('attr_test_repeat'));
		$val = new CompareValidator;
		$model = new FakedValidationModel;
		$model->attr_test = 'test-string';
		$model->attr_test_repeat = 'test-string2';
		$val->validateAttribute($model, 'attr_test');
		$this->assertTrue($model->hasErrors('attr_test'));
		$this->assertFalse($model->hasErrors('attr_test_repeat'));
	}

	public function testValidateAttributeOperators()
	{
		$value = 55;
		foreach ($this->getOperationTestData($value) as $operator => $tests) {
			$val = new CompareValidator(array('operator' => $operator, 'compareValue' => $value));
			foreach ($tests as $test) {
				$model = new FakedValidationModel;
				$model->attr_test = $test[0];
				$val->validateAttribute($model, 'attr_test');
				$this->assertEquals($test[1], !$model->hasErrors('attr_test'));
			}

		}
	}

	public function testEnsureMessageSetOnInit()
	{
		foreach ($this->getOperationTestData(1337) as $operator => $tests) {
			$val = new CompareValidator(array('operator' => $operator));
			$this->assertTrue(strlen($val->message) > 1);
		}
		try {
			$val = new CompareValidator(array('operator' => '<>'));
		} catch (InvalidConfigException $e) {
			return;
		}
		catch (\Exception $e) {
			$this->fail('InvalidConfigException expected' . get_class($e) . 'received');
			return;
		}
		$this->fail('InvalidConfigException expected none received');
	}
}