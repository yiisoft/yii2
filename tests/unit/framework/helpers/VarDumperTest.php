<?php
namespace yiiunit\framework\helpers;
use \yii\helpers\VarDumper;

class VarDumperTest extends \yii\test\TestCase
{
	public function testDumpObject()
	{
		$obj = new \StdClass();
		VarDumper::dump($obj);
	}
}