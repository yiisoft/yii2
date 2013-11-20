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
	protected function setUp()
	{
		parent::setUp();
		$this->mockApplication();
	}

	public function testValidateValue()
	{
		$validator = new EmailValidator();

		$this->assertTrue($validator->validateValue('sam@rmcreative.ru'));
		$this->assertTrue($validator->validateValue('5011@gmail.com'));
		$this->assertFalse($validator->validateValue('rmcreative.ru'));
		$this->assertFalse($validator->validateValue('Carsten Brandt <mail@cebe.cc>'));
		$this->assertFalse($validator->validateValue('"Carsten Brandt" <mail@cebe.cc>'));
		$this->assertFalse($validator->validateValue('<mail@cebe.cc>'));
		$this->assertFalse($validator->validateValue('info@örtliches.de'));
		$this->assertFalse($validator->validateValue('sam@рмкреатиф.ru'));

		$validator->allowName = true;

		$this->assertTrue($validator->validateValue('sam@rmcreative.ru'));
		$this->assertTrue($validator->validateValue('5011@gmail.com'));
		$this->assertFalse($validator->validateValue('rmcreative.ru'));
		$this->assertTrue($validator->validateValue('Carsten Brandt <mail@cebe.cc>'));
		$this->assertTrue($validator->validateValue('"Carsten Brandt" <mail@cebe.cc>'));
		$this->assertTrue($validator->validateValue('<mail@cebe.cc>'));
		$this->assertFalse($validator->validateValue('info@örtliches.de'));
		$this->assertFalse($validator->validateValue('sam@рмкреатиф.ru'));
		$this->assertFalse($validator->validateValue('Informtation info@oertliches.de'));
	}

	public function testValidateIdnValue()
	{
		if (!extension_loaded("intl")) {
			$this->markTestSkipped("intl not installed. Skipping.");
		}

		$validator = new EmailValidator();
		$validator->enableIDN = true;

		$this->assertTrue($validator->validateValue('info@örtliches.de'));
		$this->assertTrue($validator->validateValue('sam@рмкреатиф.ru'));
		$this->assertTrue($validator->validateValue('sam@rmcreative.ru'));
		$this->assertTrue($validator->validateValue('5011@gmail.com'));
		$this->assertFalse($validator->validateValue('rmcreative.ru'));
		$this->assertFalse($validator->validateValue('Carsten Brandt <mail@cebe.cc>'));
		$this->assertFalse($validator->validateValue('"Carsten Brandt" <mail@cebe.cc>'));
		$this->assertFalse($validator->validateValue('<mail@cebe.cc>'));

		$validator->allowName = true;

		$this->assertTrue($validator->validateValue('info@örtliches.de'));
		$this->assertTrue($validator->validateValue('Informtation <info@örtliches.de>'));
		$this->assertFalse($validator->validateValue('Informtation info@örtliches.de'));
		$this->assertTrue($validator->validateValue('sam@рмкреатиф.ru'));
		$this->assertTrue($validator->validateValue('sam@rmcreative.ru'));
		$this->assertTrue($validator->validateValue('5011@gmail.com'));
		$this->assertFalse($validator->validateValue('rmcreative.ru'));
		$this->assertTrue($validator->validateValue('Carsten Brandt <mail@cebe.cc>'));
		$this->assertTrue($validator->validateValue('"Carsten Brandt" <mail@cebe.cc>'));
		$this->assertTrue($validator->validateValue('<mail@cebe.cc>'));
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
		$val = new EmailValidator(['enableIDN' => true]);
		$this->assertTrue($val->validateValue('5011@example.com'));
		$this->assertTrue($val->validateValue('example@äüößìà.de'));
		$this->assertTrue($val->validateValue('example@xn--zcack7ayc9a.de'));
	}

	public function testValidateValueWithName()
	{
		$val = new EmailValidator(['allowName' => true]);
		$this->assertTrue($val->validateValue('test@example.com'));
		$this->assertTrue($val->validateValue('John Smith <john.smith@example.com>'));
		$this->assertFalse($val->validateValue('John Smith <example.com>'));
	}
}
