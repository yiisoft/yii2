<?php

namespace yiiunit\framework\validators;


use yii\validators\NumberValidator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

class NumberValidatorTest extends TestCase
{
	protected function setUp()
	{
		parent::setUp();
		$this->mockApplication();
	}

	public function testEnsureMessageOnInit()
	{
		$val = new NumberValidator;
		$this->assertTrue(is_string($val->message));
		$this->assertTrue(is_null($val->max));
		$val = new NumberValidator(['min' => -1, 'max' => 20, 'integerOnly' => true]);
		$this->assertTrue(is_string($val->message));
		$this->assertTrue(is_string($val->tooSmall));
		$this->assertTrue(is_string($val->tooBig));
	}

	public function testValidateValueSimple()
	{
		$val = new NumberValidator();
		$this->assertTrue($val->validateValue(20));
		$this->assertTrue($val->validateValue(0));
		$this->assertTrue($val->validateValue(-20));
		$this->assertTrue($val->validateValue('20'));
		$this->assertTrue($val->validateValue(25.45));
		$this->assertFalse($val->validateValue('25,45'));
		$this->assertFalse($val->validateValue('12:45'));
		$val = new NumberValidator(['integerOnly' => true]);
		$this->assertTrue($val->validateValue(20));
		$this->assertTrue($val->validateValue(0));
		$this->assertFalse($val->validateValue(25.45));
		$this->assertTrue($val->validateValue('20'));
		$this->assertFalse($val->validateValue('25,45'));
		$this->assertTrue($val->validateValue('020'));
		$this->assertTrue($val->validateValue(0x14));
		$this->assertFalse($val->validateValue('0x14')); // todo check this
	}

	public function testValidateValueAdvanced()
	{
		$val = new NumberValidator();
		$this->assertTrue($val->validateValue('-1.23')); // signed float
		$this->assertTrue($val->validateValue('-4.423e-12')); // signed float + exponent
		$this->assertTrue($val->validateValue('12E3')); // integer + exponent
		$this->assertFalse($val->validateValue('e12')); // just exponent
		$this->assertFalse($val->validateValue('-e3'));
		$this->assertFalse($val->validateValue('-4.534-e-12')); // 'signed' exponent
		$this->assertFalse($val->validateValue('12.23^4')); // expression instead of value
		$val = new NumberValidator(['integerOnly' => true]);
		$this->assertFalse($val->validateValue('-1.23'));
		$this->assertFalse($val->validateValue('-4.423e-12'));
		$this->assertFalse($val->validateValue('12E3'));
		$this->assertFalse($val->validateValue('e12'));
		$this->assertFalse($val->validateValue('-e3'));
		$this->assertFalse($val->validateValue('-4.534-e-12'));
		$this->assertFalse($val->validateValue('12.23^4'));
	}

	public function testValidateValueMin()
	{
		$val = new NumberValidator(['min' => 1]);
		$this->assertTrue($val->validateValue(1));
		$this->assertFalse($val->validateValue(-1));
		$this->assertFalse($val->validateValue('22e-12'));
		$this->assertTrue($val->validateValue(PHP_INT_MAX + 1));
		$val = new NumberValidator(['min' => 1], ['integerOnly' => true]);
		$this->assertTrue($val->validateValue(1));
		$this->assertFalse($val->validateValue(-1));
		$this->assertFalse($val->validateValue('22e-12'));
		$this->assertTrue($val->validateValue(PHP_INT_MAX + 1));
	}

	public function testValidateValueMax()
	{
		$val = new NumberValidator(['max' => 1.25]);
		$this->assertTrue($val->validateValue(1));
		$this->assertFalse($val->validateValue(1.5));
		$this->assertTrue($val->validateValue('22e-12'));
		$this->assertTrue($val->validateValue('125e-2'));
		$val = new NumberValidator(['max' => 1.25, 'integerOnly' => true]);
		$this->assertTrue($val->validateValue(1));
		$this->assertFalse($val->validateValue(1.5));
		$this->assertFalse($val->validateValue('22e-12'));
		$this->assertFalse($val->validateValue('125e-2'));
	}

	public function testValidateValueRange()
	{
		$val = new NumberValidator(['min' => -10, 'max' => 20]);
		$this->assertTrue($val->validateValue(0));
		$this->assertTrue($val->validateValue(-10));
		$this->assertFalse($val->validateValue(-11));
		$this->assertFalse($val->validateValue(21));
		$val = new NumberValidator(['min' => -10, 'max' => 20, 'integerOnly' => true]);
		$this->assertTrue($val->validateValue(0));
		$this->assertFalse($val->validateValue(-11));
		$this->assertFalse($val->validateValue(22));
		$this->assertFalse($val->validateValue('20e-1'));
	}

	public function testValidateAttribute()
	{
		$val = new NumberValidator();
		$model = new FakedValidationModel();
		$model->attr_number = '5.5e1';
		$val->validateAttribute($model, 'attr_number');
		$this->assertFalse($model->hasErrors('attr_number'));
		$model->attr_number = '43^32'; //expression
		$val->validateAttribute($model, 'attr_number');
		$this->assertTrue($model->hasErrors('attr_number'));
		$val = new NumberValidator(['min' => 10]);
		$model = new FakedValidationModel();
		$model->attr_number = 10;
		$val->validateAttribute($model, 'attr_number');
		$this->assertFalse($model->hasErrors('attr_number'));
		$model->attr_number = 5;
		$val->validateAttribute($model, 'attr_number');
		$this->assertTrue($model->hasErrors('attr_number'));
		$val = new NumberValidator(['max' => 10]);
		$model = new FakedValidationModel();
		$model->attr_number = 10;
		$val->validateAttribute($model, 'attr_number');
		$this->assertFalse($model->hasErrors('attr_number'));
		$model->attr_number = 15;
		$val->validateAttribute($model, 'attr_number');
		$this->assertTrue($model->hasErrors('attr_number'));
		$val = new NumberValidator(['max' => 10, 'integerOnly' => true]);
		$model = new FakedValidationModel();
		$model->attr_number = 10;
		$val->validateAttribute($model, 'attr_number');
		$this->assertFalse($model->hasErrors('attr_number'));
		$model->attr_number = 3.43;
		$val->validateAttribute($model, 'attr_number');
		$this->assertTrue($model->hasErrors('attr_number'));
		$val = new NumberValidator(['min' => 1]);
		$model = FakedValidationModel::createWithAttributes(['attr_num' => [1,2,3]]);
		$val->validateAttribute($model, 'attr_num');
		$this->assertTrue($model->hasErrors('attr_num'));
	}

	public function testEnsureCustomMessageIsSetOnValidateAttribute()
	{
		$val = new NumberValidator([
			'tooSmall' => '{attribute} is to small.',
			'min' => 5
		]);
		$model = new FakedValidationModel();
		$model->attr_number = 0;
		$val->validateAttribute($model, 'attr_number');
		$this->assertTrue($model->hasErrors('attr_number'));
		$this->assertEquals(1, count($model->getErrors('attr_number')));
		$msgs = $model->getErrors('attr_number');
		$this->assertSame('attr_number is to small.', $msgs[0]);
	}
}