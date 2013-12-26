<?php

namespace tests\_pages;

use yii\codeception\BasePage;

class LoginPage extends BasePage
{
	public $route = 'site/login';

	/**
	 * login form username text field locator
	 * @var string
	 */
	public $username = 'input[name="LoginForm[username]"]';
	/**
	 * login form password text field locator
	 * @var string
	 */
	public $password = 'input[name="LoginForm[password]"]';
	/**
	 * login form submit button locator
	 * @var string
	 */
	public $button = 'button[type=submit]';

	/**
	 * 
	 * @param string $username
	 * @param string $password
	 */
	public function login($username, $password)
	{
		$this->guy->fillField($this->username, $username);
		$this->guy->fillField($this->password, $password);
		$this->guy->click($this->button);
	}
}
