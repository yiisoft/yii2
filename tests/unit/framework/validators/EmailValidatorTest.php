<?php
namespace yiiunit\framework\validators;

use yii\validators\EmailValidator;
use yiiunit\data\validators\models\FakedValidationModel;
use yiiunit\TestCase;

/**
 * EmailValidatorTest
 * @group validators
 */
class EmailValidatorTest extends TestCase
{
	public function testValidateValue()
	{
		$validator = new EmailValidator();

		$this->assertTrue($validator->validateValue('sam@rmcreative.ru'));
		$this->assertTrue($validator->validateValue('5011@gmail.com'));
		$this->assertFalse($validator->validateValue('rmcreative.ru'));
	}

	public function testValidateValueMx()
	{
		$validator = new EmailValidator();
		$validator->checkMX = true;

		$this->assertTrue($validator->validateValue('5011@gmail.com'));
		$this->assertFalse($validator->validateValue('test@example.com'));
	}

	public function testValidateAttribute()
	{
		$val = new EmailValidator();
		$model = new FakedValidationModel();
		$model->attr_email = '5011@gmail.com';
		$val->validateAttribute($model, 'attr_email');
		$this->assertFalse($model->hasErrors('attr_email'));
	}

	public function testValidateValueIdn()
	{
		if (!function_exists('idn_to_ascii')) {
			$this->markTestSkipped('Intl extension required');
			return;
		}
		$val = new EmailValidator(array('enableIDN' => true));
		$this->assertTrue($val->validateValue('5011@example.com'));
		$this->assertTrue($val->validateValue('example@äüößìà.de'));
		$this->assertTrue($val->validateValue('example@xn--zcack7ayc9a.de'));
	}

	public function testValidateValueWithName()
	{
		$val = new EmailValidator(array('allowName' => true));
		$this->assertTrue($val->validateValue('test@example.com'));
		$this->assertTrue($val->validateValue('John Smith <john.smith@example.com>'));
		$this->assertFalse($val->validateValue('John Smith <example.com>'));
	}
}
