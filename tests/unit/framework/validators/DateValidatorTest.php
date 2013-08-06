<?php

namespace yiiunit\framework\validators;

use DateTime;
use yii\validators\DateValidator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;


class DateValidatorTest extends TestCase
{
	public function testEnsureMessageIsSet()
	{
		$val = new DateValidator;
		$this->assertTrue($val->message !== null && strlen($val->message) > 1);
	}

	public function testValidateValue()
	{
		$val = new DateValidator;
		$this->assertTrue($val->validateValue('2013-09-13'));
		$this->assertFalse($val->validateValue('31.7.2013'));
		$this->assertFalse($val->validateValue('31-7-2013'));
		$this->assertFalse($val->validateValue(time()));
		$val->format = 'U';
		$this->assertTrue($val->validateValue(time()));
		$val->format = 'd.m.Y';
		$this->assertTrue($val->validateValue('31.7.2013'));
		$val->format = 'Y-m-!d H:i:s';
		$this->assertTrue($val->validateValue('2009-02-15 15:16:17'));
	}

	public function testValidateAttribute()
	{
		// error-array-add
		$val = new DateValidator;
		$model = new FakedValidationModel;
		$model->attr_date = '2013-09-13';
		$val->validateAttribute($model, 'attr_date');
		$this->assertFalse($model->hasErrors('attr_date'));
		$model = new FakedValidationModel;
		$model->attr_date = '1375293913';
		$val->validateAttribute($model, 'attr_date');
		$this->assertTrue($model->hasErrors('attr_date'));
		//// timestamp attribute
		$val = new DateValidator(array('timestampAttribute' => 'attr_timestamp'));
		$model = new FakedValidationModel;
		$model->attr_date = '2013-09-13';
		$model->attr_timestamp = true;
		$val->validateAttribute($model, 'attr_date');
		$this->assertFalse($model->hasErrors('attr_date'));
		$this->assertFalse($model->hasErrors('attr_timestamp'));
		$this->assertEquals(
			DateTime::createFromFormat($val->format, '2013-09-13')->getTimestamp(),
			$model->attr_timestamp
		);


	}
}