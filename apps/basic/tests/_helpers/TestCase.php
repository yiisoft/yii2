<?php

namespace tests\_helpers;

class TestCase extends \PHPUnit_Framework_TestCase
{

	/**
	 * Your application config, will be merged with base config when creating application.
	 * @var array
	 */
	protected $config = array();

	/**
	 * Created application class
	 * @var string
	 */
	protected $appClass = '\yii\web\Application';

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
		$baseConfig = require(__DIR__.'/../unit/_bootstrap.php');
		$config = \yii\helpers\ArrayHelper::merge($baseConfig,$this->config);
		new $this->appClass($config);
	}

	protected function destroyApplication()
	{
		\Yii::$app = null;
	}

	/**
	 * Use this method when you need to dump variables with var_dump function.
	 * This is caused by the buffering output of the codeception.
	 * @param mixed $var
	 */
	protected static function varDump($var)
	{
		ob_start();
		var_dump($var);
		\Codeception\Util\Debug::debug(ob_get_clean());
	}

}
