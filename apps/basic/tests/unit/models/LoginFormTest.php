<?php

namespace tests\unit\models;

use Yii;
use yii\codeception\TestCase;
use app\models\User;

class LoginFormTest extends TestCase
{
	
	use \Codeception\Specify;

	public function testLoginNoUser()
	{
		$model = $this->mockUser(null);

		$model->username = 'some_username';
		$model->password = 'some_password';

		$this->specify('user should not be able to login, when there is no identity' , function () use ($model) {
			expect('model should not login user', $model->login())->false();
			expect('user should not be logged in', Yii::$app->user->isGuest)->true();
		});
	}

	public function testLoginWrongPassword()
	{
		$model = $this->mockUser(new User);

		$model->username = 'demo';
		$model->password = 'wrong-password';

		$this->specify('user should not be able to login with wrong password', function () use ($model) {
			expect('model should not login user', $model->login())->false();
			expect('error message should be set', $model->errors)->hasKey('password');
			expect('user should not be logged in', Yii::$app->user->isGuest)->true();
		});
	}

	public function testLoginCorrect()
	{
		$model = $this->mockUser(new User(['password' => 'demo']));

		$model->username = 'demo';
		$model->password = 'demo';

		$this->specify('user should be able to login with correct credentials', function() use ($model) {
			expect('model should login user', $model->login())->true();
			expect('error message should not be set', $model->errors)->hasntKey('password');
			expect('user should be logged in', Yii::$app->user->isGuest)->false();
		});
	}

	private function mockUser($user)
	{
		$loginForm = $this->getMock('app\models\LoginForm',['getUser']);
		$loginForm->expects($this->any())->method('getUser')->will($this->returnValue($user));
		return $loginForm;
	}

}