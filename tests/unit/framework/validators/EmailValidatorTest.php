<?php
namespace yiiunit\framework\validators;

use yii\validators\EmailValidator;
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
}
