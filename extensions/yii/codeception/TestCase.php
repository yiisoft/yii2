<?php

namespace yii\codeception;

use Yii;

/**
 * TestCase is the base class for all codeception unit tests
 *
 * @author Mark Jebri <mark.github@yandex.ru>
 * @since 2.0
 */
class TestCase extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var array the application configuration that will be used for creating an application instance for each test.
	 */
	public static $appConfig = [];
	/**
	 * @var string the application class that [[mockApplication()]] should use
	 */
	public static $appClass = 'yii\web\Application';


	/**
	 * @inheritdoc
	 */
	protected function tearDown()
	{
		$this->destroyApplication();
		parent::tearDown();
	}

	/**
	 * Mocks up the application instance.
	 * @param array $config the configuration that should be used to generate the application instance.
	 * If null, [[appConfig]] will be used.
	 * @return \yii\web\Application|\yii\console\Application the application instance
	 */
	protected function mockApplication($config = null)
	{
		return new static::$appClass($config === null ? static::$appConfig : $config);
	}

	/**
	 * Destroys the application instance created by [[mockApplication]].
	 */
	protected function destroyApplication()
	{
		Yii::$app = null;
	}
}
