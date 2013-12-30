<?php

namespace tests\unit\models;

use yii\codeception\TestCase;
use app\models\LoginForm;
use app\models\User;
use AspectMock\Test as test;

class LoginFormTest extends TestCase
{
	
	use \Codeception\Specify;

	protected function tearDown()
	{
		test::clean();
		parent::tearDown();
	}

	public function testLoginNoUser()
	{
		$user = $this->mockUser(null);

		$model = new LoginForm();
		$model->username = 'some_username';
		$model->password = 'some_password';

		$this->specify('user should not be able to login, when there is no identity' , function () use ($user,$model) {
			$this->assertFalse($model->login());
			$user->verifyInvoked('findByUsername',['some_username']);
		});
	}

	public function testLoginWrongPassword()
	{
		$this->mockUser(new User);

		$model = new LoginForm();
		$model->username = 'demo';
		$model->password = 'wrong-password';

		$this->specify('user should not be able to login with wrong password', function () use ($model){
			$this->assertFalse($model->login());
			$this->assertArrayHasKey('password',$model->errors);
		});
	}

	public function testLoginCorrect()
	{
		$this->mockUser(new User(['password' => 'demo']));

		$model = new LoginForm();
		$model->username = 'demo';
		$model->password = 'demo';

		$this->specify('user should not be able to login with correct credentials', function() use($model) {
			$this->assertTrue($model->login());
			$this->assertArrayNotHasKey('password',$model->errors);
		});
	}

	private function mockUser($user)
	{
		return test::double('app\models\User', [
			'findByUsername' => $user,
		]);
	}

}