<?php
namespace yiiunit\framework\helpers;
use \yii\helpers\VarDumper;

class VarDumperTest extends \yii\test\TestCase
{
	public function testDumpObject()
	{
		$obj = new \StdClass();
		ob_start();
		VarDumper::dump($obj);
		$this->assertEquals("stdClass#1\n(\n)", ob_get_contents());
		ob_end_clean();
	}
}
