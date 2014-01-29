<?php

namespace yii\codeception;

use Yii;
use yii\base\InvalidConfigException;
use Codeception\TestCase\Test;
use yii\test\FixtureTrait;

/**
 * TestCase is the base class for all codeception unit tests
 *
 * @author Mark Jebri <mark.github@yandex.ru>
 * @since 2.0
 */
class TestCase extends Test
{
	use FixtureTrait;

	/**
	 * @var array|string the application configuration that will be used for creating an application instance for each test.
	 * You can use a string to represent the file path or path alias of a configuration file.
	 * The application configuration array may contain an optional `class` element which specifies the class
	 * name of the application instance to be created. By default, a [[\yii\web\Application]] instance will be created.
	 */
	public $appConfig = '@tests/unit/_config.php';

	/**
	 * @inheritdoc
	 */
	protected function setUp()
	{
		parent::setUp();
		$this->mockApplication();
		$this->loadFixtures();
	}

	/**
	 * @inheritdoc
	 */
	protected function tearDown()
	{
		$this->unloadFixtures();
		$this->destroyApplication();
		parent::tearDown();
	}

	/**
	 * Mocks up the application instance.
	 * @param array $config the configuration that should be used to generate the application instance.
	 * If null, [[appConfig]] will be used.
	 * @return \yii\web\Application|\yii\console\Application the application instance
	 * @throws InvalidConfigException if the application configuration is invalid
	 */
	protected function mockApplication($config = null)
	{
		$config = $config === null ? $this->appConfig : $config;
		if (is_string($config)) {
			$configFile = Yii::getAlias($config);
			if (!is_file($configFile)) {
				throw new InvalidConfigException("The application configuration file does not exist: $config");
			}
			$config = require($configFile);
		}
		if (is_array($config)) {
			if (!isset($config['class'])) {
				$config['class'] = 'yii\web\Application';
			}
			return Yii::createObject($config);
		} else {
			throw new InvalidConfigException('Please provide a configuration array to mock up an application.');
		}
	}

	/**
	 * Destroys the application instance created by [[mockApplication]].
	 */
	protected function destroyApplication()
	{
		Yii::$app = null;
	}
}
