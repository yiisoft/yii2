<?php

namespace tests\_pages;

class BasePage
{

	// include url of current page
	public static $URL = '';

	/**
	 * Declare UI map for this page here. CSS or XPath allowed.
	 * public static $usernameField = '#username';
	 * public static $formSubmitButton = "#mainForm input[type=submit]";
	 */

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
	 * @var
	 */
	protected $guy;

	public function __construct($I)
	{
		$this->guy = $I;
	}

	/**
	 * @return $this
	 */
	public static function of($I)
	{
		return new static($I);
	}

}
