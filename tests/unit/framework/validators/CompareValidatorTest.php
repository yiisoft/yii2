<?php
namespace yiiunit\framework\validators;

use yii\base\InvalidConfigException;
use yii\validators\CompareValidator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;



class CompareValidatorTest extends TestCase
{
	protected function setUp()
	{
		parent::setUp();
		$this->mockApplication();
	}

	public function testValidateValueException()
	{
		$this->setExpectedException('yii\base\InvalidConfigException');
		$val = new CompareValidator;
		$val->validate('val');
	}

	public function testValidateValue()
	{
		$value = 18449;
		// default config
		$val = new CompareValidator(['compareValue' => $value]);
		$this->assertTrue($val->validate($value));
		$this->assertTrue($val->validate((string)$value));
		$this->assertFalse($val->validate($value + 1));
		foreach ($this->getOperationTestData($value) as $op => $tests) {
			$val = new CompareValidator(['compareValue' => $value]);
			$val->operator = $op;
			foreach ($tests as $test) {
				$this->assertEquals($test[1], $val->validate($test[0]));
			}
		}
	}

	protected function getOperationTestData($value)
	{
		return [
			'===' => [
				[$value, true],
				[(string)$value, false],
				[(float)$value, false],
				[$value + 1, false],
			],
			'!=' => [
				[$value, false],
				[(string)$value, false],
				[(float)$value, false],
				[$value + 0.00001, true],
				[false, true],
			],
			'!==' => [
				[$value, false],
				[(string)$value, true],
				[(float)$value, true],
				[false, true],
			],
			'>' => [
				[$value, false],
				[$value + 1, true],
				[$value - 1, false],
			],
			'>=' => [
				[$value, true],
				[$value + 1, true],
				[$value - 1, false],
			],
			'<' => [
				[$value, false],
				[$value + 1, false],
				[$value - 1, true],
			],
			'<=' => [
				[$value, true],
				[$value + 1, false],
				[$value - 1, true],
			],
			//'non-op' => [
			//	[$value, false],
			//	[$value + 1, false],
			//	[$value - 1, false],
			//],
		];
	}

	public function testValidateAttribute()
	{
		// invalid-array
		$val = new CompareValidator;
		$model = new FakedValidationModel;
		$model->attr = ['test_val'];
		$val->validateAttribute($model, 'attr');
		$this->assertTrue($model->hasErrors('attr'));
		$val = new CompareValidator(['compareValue' => 'test-string']);
		$model = new FakedValidationModel;
		$model->attr_test = 'test-string';
		$val->validateAttribute($model, 'attr_test');
		$this->assertFalse($model->hasErrors('attr_test'));
		$val = new CompareValidator(['compareAttribute' => 'attr_test_val']);
		$model = new FakedValidationModel;
		$model->attr_test = 'test-string';
		$model->attr_test_val = 'test-string';
		$val->validateAttribute($model, 'attr_test');
		$this->assertFalse($model->hasErrors('attr_test'));
		$this->assertFalse($model->hasErrors('attr_test_val'));
		$val = new CompareValidator(['compareAttribute' => 'attr_test_val']);
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
		// not existing op
		$val = new CompareValidator();
		$val->operator = '<>';
		$model = FakedValidationModel::createWithAttributes(['attr_o' => 5, 'attr_o_repeat' => 5]);
		$val->validateAttribute($model, 'attr_o');
		$this->assertTrue($model->hasErrors('attr_o'));
	}

	public function testValidateAttributeOperators()
	{
		$value = 55;
		foreach ($this->getOperationTestData($value) as $operator => $tests) {
			$val = new CompareValidator(['operator' => $operator, 'compareValue' => $value]);
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
			$val = new CompareValidator(['operator' => $operator]);
			$this->assertTrue(strlen($val->message) > 1);
		}
		try {
			new CompareValidator(['operator' => '<>']);
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
