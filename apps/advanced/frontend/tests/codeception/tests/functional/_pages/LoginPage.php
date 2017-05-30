<?php

namespace frontend\tests\codeception\tests\functional\_pages;

require_once (__DIR__ .'/../../_pages/LoginPage.php');

class LoginPage extends \frontend\tests\codeception\tests\_pages\LoginPage
{

	/**
	 * login form username text field locator
	 * @var string
	 */
	public $username = 'LoginForm[username]';

	/**
	 * login form password text field locator
	 * @var string
	 */
	public $password = 'LoginForm[password]';

	/**
	 * 
	 * @param string $username
	 * @param string $password
	 */
	public function login($username, $password)
	{
		$this->guy->submitForm('#login-form',[
			$this->username	=>	$username,
			$this->password	=>	$password,
		]);
	}

}
