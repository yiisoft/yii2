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
		$this->destroyApp();
	}
	
	public function getParam($name,$default=null)
	{
		if (self::$params === null) {
			self::$params = require(__DIR__ . '/data/config.php');
		}
		return isset(self::$params[$name]) ? self::$params[$name] : $default;
	}
	
	protected function mockApplication($requiredConfig=array())
	{
		static $defaultConfig = array(
			'id' => 'testapp',
			'basePath' => __DIR__,
		);
		
		$appClass = $this->getParam( 'appClass', '\yii\web\Application' );
		new $appClass(array_merge($defaultConfig,$requiredConfig));
	}
	
	protected function destroyApp()
	{
		\Yii::$app = null;
	}
}
