<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\test;

use yii\test\Fixture;
use yii\test\FixtureTrait;
use yiiunit\TestCase;

class Fixture1 extends Fixture
{
	public $depends = ['yiiunit\framework\test\Fixture2'];

	public function load()
	{
		MyTestCase::$load .= '1';
	}

	public function unload()
	{
		MyTestCase::$unload .= '1';
	}
}

class Fixture2 extends Fixture
{
	public $depends = ['yiiunit\framework\test\Fixture3'];
	public function load()
	{
		MyTestCase::$load .= '2';
	}


	public function unload()
	{
		MyTestCase::$unload .= '2';
	}
}

class Fixture3 extends Fixture
{
	public function load()
	{
		MyTestCase::$load .= '3';
	}


	public function unload()
	{
		MyTestCase::$unload .= '3';
	}
}

class MyTestCase
{
	use FixtureTrait;

	public $scenario = 1;
	public static $load;
	public static $unload;

	public function setUp()
	{
		$this->loadFixtures();
	}

	public function tearDown()
	{
		$this->unloadFixtures();
	}

	public function fetchFixture($name)
	{
		return $this->getFixture($name);
	}

	public function fixtures()
	{
		switch ($this->scenario) {
			case 0: return [];
			case 1: return [
				'fixture1' => Fixture1::className(),
			];
			case 2: return [
				'fixture2' => Fixture2::className(),
			];
			case 3: return [
				'fixture3' => Fixture3::className(),
			];
			case 4: return [
				'fixture1' => Fixture1::className(),
				'fixture2' => Fixture2::className(),
			];
			case 5: return [
				'fixture2' => Fixture2::className(),
				'fixture3' => Fixture3::className(),
			];
			case 6: return [
				'fixture1' => Fixture1::className(),
				'fixture3' => Fixture3::className(),
			];
			case 7:
			default: return [
				'fixture1' => Fixture1::className(),
				'fixture2' => Fixture2::className(),
				'fixture3' => Fixture3::className(),
			];
		}
	}
}

class FixtureTest extends TestCase
{
	public function testDependencies()
	{
		foreach ($this->getDependencyTests() as $scenario => $result) {
			$test = new MyTestCase();
			$test->scenario = $scenario;
			$test->setUp();
			foreach ($result as $name => $loaded) {
				$this->assertEquals($loaded, $test->fetchFixture($name) !== null, "Verifying scenario $scenario fixture $name");
			}
		}
	}

	public function testLoadSequence()
	{
		foreach ($this->getLoadSequenceTests() as $scenario => $result) {
			$test = new MyTestCase();
			$test->scenario = $scenario;
			MyTestCase::$load = '';
			MyTestCase::$unload = '';
			$test->setUp();
			$this->assertEquals($result[0], MyTestCase::$load, "Verifying scenario $scenario load sequence");
			$test->tearDown();
			$this->assertEquals($result[1], MyTestCase::$unload, "Verifying scenario $scenario unload sequence");
		}
	}

	protected function getDependencyTests()
	{
		return [
			0 => ['fixture1' => false, 'fixture2' => false, 'fixture3' => false],
			1 => ['fixture1' => true, 'fixture2' => false, 'fixture3' => false],
			2 => ['fixture1' => false, 'fixture2' => true, 'fixture3' => false],
			3 => ['fixture1' => false, 'fixture2' => false, 'fixture3' => true],
			4 => ['fixture1' => true, 'fixture2' => true, 'fixture3' => false],
			5 => ['fixture1' => false, 'fixture2' => true, 'fixture3' => true],
			6 => ['fixture1' => true, 'fixture2' => false, 'fixture3' => true],
			7 => ['fixture1' => true, 'fixture2' => true, 'fixture3' => true],
		];
	}

	protected function getLoadSequenceTests()
	{
		return [
			0 => ['', ''],
			1 => ['321', '123'],
			2 => ['32', '23'],
			3 => ['3', '3'],
			4 => ['321', '123'],
			5 => ['32', '23'],
			6 => ['321', '123'],
			7 => ['321', '123'],
		];
	}
}
