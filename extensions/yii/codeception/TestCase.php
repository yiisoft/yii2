<?php

namespace yii\codeception;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * TestCase is the base class for all codeception unit tests
 *
 * @author Mark Jebri <mark.github@yandex.ru>
 * @since 2.0
 */
class TestCase extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var array|string Your application base config that will be used for creating application each time before test.
	 * This can be an array or alias, pointing to the config file. For example for console application it can be
	 * '@tests/unit/console_bootstrap.php' that can be similar to existing unit tests bootstrap file.
	 */
	public static $applicationConfig = '@app/config/web.php';
	/**
	 * @var array|string Your application config, will be merged with base config when creating application. Can be an alias too.
	 */
	protected $config = [];

	/**
	 * Created application class
	 * @var string
	 */
	protected $applicationClass = 'yii\web\Application';

	protected function tearDown()
	{
		$this->destroyApplication();
		parent::tearDown();
	}

	/**
	 * Sets up `Yii::$app`.
	 */
	protected function mockApplication()
	{
		$baseConfig = is_array(static::$applicationConfig) ? static::$applicationConfig : require(Yii::getAlias(static::$applicationConfig));
		$config = is_array($this->config)? $this->config : require(Yii::getAlias($this->config));
		new $this->applicationClass(ArrayHelper::merge($baseConfig,$config));
	}

	/**
	 * Destroys an application created via [[mockApplication]].
	 */
	protected function destroyApplication()
	{
		\Yii::$app = null;
	}
}
