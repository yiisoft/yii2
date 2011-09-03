<?php

class TestCase extends \yii\test\TestCase
{
	public $params;

	function getParam($name)
	{
		if ($this->params === null) {
			$this->params = require(__DIR__ . '/data/config.php');
		}
		return isset($this->params[$name]) ? $this->params[$name] : null;
	}
}