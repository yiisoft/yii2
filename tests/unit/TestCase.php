<?php

namespace yiiunit;

class TestCase extends \yii\test\TestCase
{
	public static $params;

	protected function setUp() {
		parent::setUp();
	}

	protected function tearDown()
	{
		parent::tearDown();
		// If defined and set to true, destroy the app after each test.
		// This will cause tests to fail, that rely on an existing app, but don't
		// call requireApp() in their setUp().
		if (defined('YII_DESTROY_APP_ON_TEARDOWN') && YII_DESTROY_APP_ON_TEARDOWN === true) {
			$this->destroyApp();
		}
	}
	
	public function getParam($name,$default=null)
	{
		if (self::$params === null) {
			self::$params = require(__DIR__ . '/data/config.php');
		}
		return isset(self::$params[$name]) ? self::$params[$name] : $default;
	}
	
	protected function requireApp($requiredConfig=array())
	{
		static $usedConfig = array();
		static $defaultConfig = array(
			'id' => 'testapp',
			'basePath' => __DIR__,
		);
		
		$newConfig = array_merge( $defaultConfig, $requiredConfig );
		$appClass = $this->getParam( 'appClass', '\yii\web\Application' );
		
		if (!(\yii::$app instanceof $appClass)) {
			new $appClass( $newConfig );
			$usedConfig = $newConfig;
		} elseif ($newConfig !== $usedConfig) {
			new $appClass( $newConfig );
			$usedConfig = $newConfig;
		}
	}
	
	protected function destroyApp()
	{
		\yii::$app = null;
	}
}
