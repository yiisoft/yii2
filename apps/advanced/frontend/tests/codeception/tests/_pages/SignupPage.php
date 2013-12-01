<?php

namespace frontend\tests\codeception\tests\_pages;

class SignupPage extends BasePage
{

	/**
	 * signup form username text field locator
	 * @var string
	 */
	public $username = 'input[name="User[username]"]';

	/**
	 * signup form email text field locator
	 * @var string
	 */
	public $email = 'input[name="User[email]"]';

	/**
	 * signup form password text field locator
	 * @var string
	 */
	public $password = 'input[name="User[password]"]';

	/**
	 * signup form button locator
	 * @var string
	 */
	public $button = 'button[type=submit]';

	/**
	 * Signups current user with the given data
	 * @param array $signupData
	 */
	public function signup(array $signupData)
	{
		if (!empty($signupData))
		{
			$this->guy->fillField($this->username,$signupData['username']);
			$this->guy->fillField($this->email,$signupData['email']);
			$this->guy->fillField($this->password,$signupData['password']);
		}
		$this->guy->click($this->button);
	}

}
