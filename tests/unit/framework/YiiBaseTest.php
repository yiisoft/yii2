<?php
namespace yiiunit\framework;

use Yii;
use yiiunit\TestCase;

/**
 * YiiBaseTest
 */
class YiiBaseTest extends TestCase
{
	public $aliases;

	protected function setUp()
	{
		parent::setUp();
		$this->aliases = Yii::$aliases;
	}

	protected function tearDown()
	{
		parent::tearDown();
		Yii::$aliases = $this->aliases;
	}

	public function testAlias()
	{
		$this->assertEquals(YII_PATH, Yii::getAlias('@yii'));

		Yii::$aliases = array();
		$this->assertFalse(Yii::getAlias('@yii', false));

		Yii::setAlias('@yii', '/yii/framework');
		$this->assertEquals('/yii/framework', Yii::getAlias('@yii'));
		$this->assertEquals('/yii/framework/test/file', Yii::getAlias('@yii/test/file'));
		Yii::setAlias('@yii/gii', '/yii/gii');
		$this->assertEquals('/yii/framework', Yii::getAlias('@yii'));
		$this->assertEquals('/yii/framework/test/file', Yii::getAlias('@yii/test/file'));
		$this->assertEquals('/yii/gii', Yii::getAlias('@yii/gii'));
		$this->assertEquals('/yii/gii/file', Yii::getAlias('@yii/gii/file'));

		Yii::setAlias('@tii', '@yii/test');
		$this->assertEquals('/yii/framework/test', Yii::getAlias('@tii'));

		Yii::setAlias('@yii', null);
		$this->assertFalse(Yii::getAlias('@yii', false));
		$this->assertEquals('/yii/gii/file', Yii::getAlias('@yii/gii/file'));
	}

	public function testGetVersion()
	{
		$this->assertTrue((boolean)preg_match('~\d+\.\d+(?:\.\d+)?(?:-\w+)?~', \Yii::getVersion()));
	}

	public function testPowered()
	{
		$this->assertTrue(is_string(Yii::powered()));
	}
}
