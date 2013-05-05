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
}
