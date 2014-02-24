<?php

namespace frontend\tests\unit\models;

use frontend\tests\unit\DbTestCase;
use common\tests\fixtures\UserFixture;

class SignupFormTest extends DbTestCase
{

	use \Codeception\Specify;

	public function testCorrectSignup()
	{
		$model = $this->getMock('frontend\models\SignupForm',['validate']);
		$model->expects($this->once())->method('validate')->will($this->returnValue(true));

		$model->username = 'some_username';
		$model->email = 'some_email@example.com';
		$model->password = 'some_password';

		$user = $model->signup();
		$this->assertInstanceOf('common\models\User', $user);
		expect('username should be correct', $user->username)->equals('some_username');
		expect('email should be correct', $user->email)->equals('some_email@example.com');
		expect('password should be correct', $user->validatePassword('some_password'))->true();
	}

	public function testNotCorrectSignup()
	{
		$model = $this->getMock('frontend\models\SignupForm',['validate']);
		$model->expects($this->once())->method('validate')->will($this->returnValue(false));

		expect('user should not be created', $model->signup())->null();
	}

	public function fixtures()
	{
		return [
			'user' => [
				'class' => UserFixture::className(),
				'dataFile' => false, //do not load test data, only table cleanup
			],
		];
	}

}
