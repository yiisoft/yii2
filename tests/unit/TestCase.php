<?php

namespace yiiunit;

class TestCase extends \yii\test\TestCase
{
	public static $params;

	public function getParam($name)
	{
		if (self::$params === null) {
			self::$params = require(__DIR__ . '/data/config.php');
		}
		return isset(self::$params[$name]) ? self::$params[$name] : null;
	}
	
	protected function requireApp($requiredConfig=array())
	{
		static $usedConfig = array();
		static $defaultConfig = array(
			'id' => 'testapp',
			'basePath' => __DIR__,
		);
		
		$newConfig = array_merge( $defaultConfig, $requiredConfig );
		
		if (!(\yii::$app instanceof \yii\web\Application)) {
			new \yii\web\Application( $newConfig );
			$usedConfig = $newConfig;
		} elseif ($newConfig !== $usedConfig) {
			new \yii\web\Application( $newConfig );
			$usedConfig = $newConfig;
		}
	}
}
