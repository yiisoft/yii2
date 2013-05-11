<?php

namespace yiiunit;

/**
 * This is the base class for all yii framework unit tests.
 */
class TestCase extends \yii\test\TestCase
{
	public static $params;

	/**
	 * Clean up after test.
	 * By default the application created with [[mockApplication]] will be destroyed.
	 */
	protected function tearDown()
	{
		parent::tearDown();
		$this->destroyApplication();
	}

	/**
	 * Returns a test configuration param from /data/config.php
	 * @param string $name params name
	 * @param mixed $default default value to use when param is not set.
	 * @return mixed the value of the configuration param
	 */
	public function getParam($name, $default = null)
	{
		if (self::$params === null) {
			self::$params = require(__DIR__ . '/data/config.php');
		}
		return isset(self::$params[$name]) ? self::$params[$name] : $default;
	}

	/**
	 * Populates Yii::$app with a new application
	 * The application will be destroyed on tearDown() automatically.
	 * @param array $config The application configuration, if needed
	 */
	protected function mockApplication($config=array())
	{
		static $defaultConfig = array(
			'id' => 'testapp',
			'basePath' => __DIR__,
		);
		
		$appClass = $this->getParam( 'appClass', '\yii\web\Application' );
		new $appClass(array_merge($defaultConfig,$config));
	}
	
	/**
	 * Destroys application in Yii::$app by setting it to null.
	 */
	protected function destroyApplication()
	{
		\Yii::$app = null;
	}
}
