<?php

namespace yii\codeception;

use Yii;

class TestCase extends \PHPUnit_Framework_TestCase
{
	/**
	 * Your application base config that will be used for creating application each time before test.
	 * This can be an array or alias, pointing to the config file. For example for console application it can be
	 * '@tests/unit/console_bootstrap.php' that can be similar to existing unit tests bootstrap file.
	 * @var mixed
	 */
	protected $baseConfig = '@app/config/web.php';

	/**
	 * Your application config, will be merged with base config when creating application. Can be an alias too.
	 * @var mixed
	 */
	protected $config = [];

	/**
	 * Created application class
	 * @var string
	 */
	protected $appClass = 'yii\web\Application';

	protected function setUp()
	{
		parent::setUp();
		$this->mockApplication();
	}

	protected function tearDown()
	{
		$this->destroyApplication();
		parent::tearDown();
	}

	protected function mockApplication()
	{
		$baseConfig = is_array($this->baseConfig) ? $this->baseConfig : require(Yii::getAlias($this->baseConfig));
		$config = is_array($this->config)? $this->config : require(Yii::getAlias($this->config));
		new $this->appClass(\yii\helpers\ArrayHelper::merge($baseConfig,$config));
	}

	protected function destroyApplication()
	{
		\Yii::$app = null;
	}

}
