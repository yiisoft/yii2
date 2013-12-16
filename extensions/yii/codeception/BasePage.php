<?php

namespace yii\codeception;

use Codeception\AbstractGuy;

/**
 *
 * Declare UI map for this page here. CSS or XPath allowed.
 * public static $usernameField = '#username';
 * public static $formSubmitButton = "#mainForm input[type=submit]";
 *
 * @author Mark Jebri <mark.github@yandex.ru>
 * @since 2.0
 */
abstract class BasePage
{
	/**
	 * @var string include url of current page. This property has to be overwritten by subclasses
	 */
	public static $URL = '';
	/**
	 * @var AbstractGuy
	 */
	protected $guy;

	public function __construct($I)
	{
		$this->guy = $I;
	}

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
	 * @param $I
	 * @return static
	 */
	public static function of($I)
	{
		return new static($I);
	}
}
