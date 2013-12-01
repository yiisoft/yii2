<?php

class LoginPage
{
	/**
	 * username text field locator
	 * @var string
	 */
	public $username = 'input[name="LoginForm[username]"]';

	/**
	 * password text field locator
	 * @var string
	 */
	public $password = 'input[name="LoginForm[password]"]';

	/**
	 * submit button locator
	 * @var string
	 */
	public $submit = 'button[type=submit]';

    // include url of current page
    static $URL = '';

    /**
     * Basic route example for your current URL
     * You can append any additional parameter to URL
     * and use it in tests like: EditPage::route('/123-post');
     */
    public static function route($param)
    {
		return static::$URL.$param;
	}

	/**
	 *
	 * @var WebGuy
	 */
	protected $guy;

	public function __construct($I)
	{
		$this->guy = $I;
	}

    /**
     * @return LoginPage
     */
	public static function of($I)
	{
		return new static($I);
	}

	public function login($username,$password)
	{
		$this->guy->fillField($this->username, $username);
		$this->guy->fillField($this->password, $password);
		$this->guy->click($this->submit);
	}

}
