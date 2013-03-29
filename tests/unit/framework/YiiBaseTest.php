<?php
namespace yiiunit\framework;

use yiiunit\TestCase;

/**
 * YiiBaseTest
 */
class YiiBaseTest extends TestCase
{
	public function testAlias()
	{

	}

	public function testGetVersion()
	{
		echo \Yii::getVersion();
		$this->assertTrue((boolean)preg_match('~\d+\.\d+(?:\.\d+)?(?:-\w+)?~', \Yii::getVersion()));
	}

	public function testPowered()
	{
		$this->assertTrue(is_string(\Yii::powered()));
	}
}
