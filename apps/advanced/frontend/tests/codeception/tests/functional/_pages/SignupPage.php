<?php

namespace frontend\tests\codeception\tests\functional\_pages;

require_once (__DIR__ .'/../../_pages/SignupPage.php');

class SignupPage extends \frontend\tests\codeception\tests\_pages\SignupPage
{

	/**
	 * signup form username text field locator
	 * @var string
	 */
	public $username = 'User[username]';

	/**
	 * signup form email text field locator
	 * @var string
	 */
	public $email = 'User[email]';

	/**
	 * signup form password text field locator
	 * @var string
	 */
	public $password = 'User[password]';

	/**
	 * Signups current user with the given data
	 * @param array $signupData
	 */
	public function signup(array $signupData)
	{
		if (empty($signupData))
			$this->guy->submitForm('#form-signup',[]);
		else
			$this->guy->submitForm('#form-signup',[
				$this->username	=>	$signupData['username'],
				$this->email	=>	$signupData['email'],
				$this->password	=>	$signupData['password'],
			]);
	}

}
