<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
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

class Fixture4 extends Fixture
{
    public $depends = ['yiiunit\framework\test\Fixture5'];
    public function load()
    {
        MyTestCase::$load .= '4';
    }

    public function unload()
    {
        MyTestCase::$unload .= '4';
    }
}

class Fixture5 extends Fixture
{
    public $depends = ['yiiunit\framework\test\Fixture4'];
    public function load()
    {
        MyTestCase::$load .= '5';
    }

    public function unload()
    {
        MyTestCase::$unload .= '5';
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
            case 7: return [
                'fixture1' => Fixture1::className(),
                'fixture2' => Fixture2::className(),
                'fixture3' => Fixture3::className(),
            ];
            case 8: return [
                'fixture4' => Fixture4::className(),
            ];
            case 9: return [
                'fixture5' => Fixture5::className(),
                'fixture4' => Fixture4::className(),
            ];
            case 10: return [
                'fixture3a' => Fixture3::className(), // duplicate fixtures may occur two fixtures depend on the same fixture.
                'fixture3b' => Fixture3::className(),
            ];
            default: return [];
        }
    }
}

/**
 * @group fixture
 */
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
            0 => ['fixture1' => false, 'fixture2' => false, 'fixture3' => false, 'fixture4' => false, 'fixture5' => false],
            1 => ['fixture1' => true, 'fixture2' => false, 'fixture3' => false, 'fixture4' => false, 'fixture5' => false],
            2 => ['fixture1' => false, 'fixture2' => true, 'fixture3' => false, 'fixture4' => false, 'fixture5' => false],
            3 => ['fixture1' => false, 'fixture2' => false, 'fixture3' => true, 'fixture4' => false, 'fixture5' => false],
            4 => ['fixture1' => true, 'fixture2' => true, 'fixture3' => false, 'fixture4' => false, 'fixture5' => false],
            5 => ['fixture1' => false, 'fixture2' => true, 'fixture3' => true, 'fixture4' => false, 'fixture5' => false],
            6 => ['fixture1' => true, 'fixture2' => false, 'fixture3' => true, 'fixture4' => false, 'fixture5' => false],
            7 => ['fixture1' => true, 'fixture2' => true, 'fixture3' => true, 'fixture4' => false, 'fixture5' => false],
            8 => ['fixture1' => false, 'fixture2' => false, 'fixture3' => false, 'fixture4' => true, 'fixture5' => false],
            9 => ['fixture1' => false, 'fixture2' => false, 'fixture3' => false, 'fixture4' => true, 'fixture5' => true],
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
            8 => ['54', '45'],
            9 => ['45', '54'],
            10 => ['3', '3'],
        ];
    }
}
