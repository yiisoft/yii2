<?php

namespace yii\codeception;

use Yii;
use yii\base\InvalidConfigException;
use Codeception\TestCase\Test;

/**
 * TestCase is the base class for all codeception unit tests
 *
 * @author Mark Jebri <mark.github@yandex.ru>
 * @since 2.0
 */
class TestCase extends Test
{
	/**
	 * @var array|string the application configuration that will be used for creating an application instance for each test.
	 * You can use a string to represent the file path or path alias of a configuration file.
	 */
	public static $appConfig = [];
	/**
	 * @var string the application class that [[mockApplication()]] should use
	 */
	public static $appClass = 'yii\web\Application';

	/**
	 * @inheritdoc
	 */
	protected function setUp()
	{
		parent::setUp();
		$this->mockApplication();
	}

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
		$config = $config === null ? static::$appConfig : $config;
		if (is_string($config)) {
			$config = Yii::getAlias($config);
		}
		if (!is_array($config)) {
			throw new InvalidConfigException('Please provide a configuration for creating application.');
		}
		return new static::$appClass($config);
	}

	/**
	 * Destroys the application instance created by [[mockApplication]].
	 */
	protected function destroyApplication()
	{
		Yii::$app = null;
	}
}
