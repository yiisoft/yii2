<?php

namespace yiiunit\framework\console\controllers;

use Yii;
use yiiunit\TestCase;
use yiiunit\data\console\controllers\fixtures\FixtureStorage;
use yii\console\controllers\FixtureController;

/**
 * Unit test for [[\yii\console\controllers\FixtureController]].
 * @see FixtureController
 *
 * @group console
 */
class FixtureControllerTest extends TestCase
{

    /**
     * @var \yiiunit\framework\console\controllers\FixtureConsoledController
     */
    private $_fixtureController;

    protected function setUp()
    {
        parent::setUp();

        $this->_fixtureController = Yii::createObject([
            'class' => 'yiiunit\framework\console\controllers\FixtureConsoledController',
            'interactive' => false,
            'globalFixtures' => [],
            'namespace' => 'yiiunit\data\console\controllers\fixtures',
        ],[null, null]); //id and module are null
    }

    protected function tearDown()
    {
        $this->_fixtureController = null;
        FixtureStorage::clear();

        parent::tearDown();
    }

    public function testLoadGlobalFixture()
    {
        $this->_fixtureController->globalFixtures = [
            '\yiiunit\data\console\controllers\fixtures\Global'
        ];

        $this->_fixtureController->actionLoad('First');

        $this->assertCount(1, FixtureStorage::$globalFixturesData, 'global fixture data should be loaded');
        $this->assertCount(1, FixtureStorage::$firstFixtureData, 'first fixture data should be loaded');
    }

    public function testUnloadGlobalFixture()
    {
        $this->_fixtureController->globalFixtures = [
            '\yiiunit\data\console\controllers\fixtures\Global'
        ];

        FixtureStorage::$globalFixturesData[] = 'some seeded global fixture data';
        FixtureStorage::$firstFixtureData[] = 'some seeded first fixture data';

        $this->assertCount(1, FixtureStorage::$globalFixturesData, 'global fixture data should be loaded');
        $this->assertCount(1, FixtureStorage::$firstFixtureData, 'first fixture data should be loaded');

        $this->_fixtureController->actionUnload('First');

        $this->assertEmpty(FixtureStorage::$globalFixturesData, 'global fixture data should be unloaded');
        $this->assertEmpty(FixtureStorage::$firstFixtureData, 'first fixture data should be unloaded');
    }

    public function testLoadAll()
    {
        $this->assertEmpty(FixtureStorage::$globalFixturesData, 'global fixture data should be empty');
        $this->assertEmpty(FixtureStorage::$firstFixtureData, 'first fixture data should be empty');
        $this->assertEmpty(FixtureStorage::$secondFixtureData, 'second fixture data should be empty');

        $this->_fixtureController->actionLoad('all');

        $this->assertCount(1, FixtureStorage::$globalFixturesData, 'global fixture data should be loaded');
        $this->assertCount(1, FixtureStorage::$firstFixtureData, 'first fixture data should be loaded');
        $this->assertCount(1, FixtureStorage::$secondFixtureData, 'second fixture data should be loaded');
    }

    public function testUnloadAll()
    {
        FixtureStorage::$globalFixturesData[] = 'some seeded global fixture data';
        FixtureStorage::$firstFixtureData[] = 'some seeded first fixture data';
        FixtureStorage::$secondFixtureData[] = 'some seeded second fixture data';

        $this->assertCount(1, FixtureStorage::$globalFixturesData, 'global fixture data should be loaded');
        $this->assertCount(1, FixtureStorage::$firstFixtureData, 'first fixture data should be loaded');
        $this->assertCount(1, FixtureStorage::$secondFixtureData, 'second fixture data should be loaded');

        $this->_fixtureController->actionUnload('all');

        $this->assertEmpty(FixtureStorage::$globalFixturesData, 'global fixture data should be unloaded');
        $this->assertEmpty(FixtureStorage::$firstFixtureData, 'first fixture data should be unloaded');
        $this->assertEmpty(FixtureStorage::$secondFixtureData, 'second fixture data should be unloaded');
    }

    public function testLoadParticularExceptOnes()
    {
        $this->_fixtureController->actionLoad('First', '-Second', '-Global');

        $this->assertCount(1, FixtureStorage::$firstFixtureData, 'first fixture data should be loaded');
        $this->assertEmpty(FixtureStorage::$globalFixturesData, 'global fixture data should not be loaded');
        $this->assertEmpty(FixtureStorage::$secondFixtureData, 'second fixture data should not be loaded');
    }

    public function testUnloadParticularExceptOnes()
    {
        FixtureStorage::$globalFixturesData[] = 'some seeded global fixture data';
        FixtureStorage::$firstFixtureData[] = 'some seeded first fixture data';
        FixtureStorage::$secondFixtureData[] = 'some seeded second fixture data';

        $this->_fixtureController->actionUnload('First', '-Second', '-Global');

        $this->assertEmpty(FixtureStorage::$firstFixtureData, 'first fixture data should be unloaded');
        $this->assertNotEmpty(FixtureStorage::$globalFixturesData, 'global fixture data should not be unloaded');
        $this->assertNotEmpty(FixtureStorage::$secondFixtureData, 'second fixture data should not be unloaded');
    }

    public function testLoadAllExceptOnes()
    {
        $this->_fixtureController->actionLoad('all', '-Second', '-Global');

        $this->assertCount(1, FixtureStorage::$firstFixtureData, 'first fixture data should be loaded');
        $this->assertEmpty(FixtureStorage::$globalFixturesData, 'global fixture data should not be loaded');
        $this->assertEmpty(FixtureStorage::$secondFixtureData, 'second fixture data should not be loaded');
    }

    public function testUnloadAllExceptOnes()
    {
        FixtureStorage::$globalFixturesData[] = 'some seeded global fixture data';
        FixtureStorage::$firstFixtureData[] = 'some seeded first fixture data';
        FixtureStorage::$secondFixtureData[] = 'some seeded second fixture data';

        $this->_fixtureController->actionUnload('all', '-Second', '-Global');

        $this->assertEmpty(FixtureStorage::$firstFixtureData, 'first fixture data should be unloaded');
        $this->assertNotEmpty(FixtureStorage::$globalFixturesData, 'global fixture data should not be unloaded');
        $this->assertNotEmpty(FixtureStorage::$secondFixtureData, 'second fixture data should not be unloaded');
    }

    public function testNothingToLoadParticularExceptOnes()
    {
        $this->_fixtureController->actionLoad('First', '-First');

        $this->assertEmpty(FixtureStorage::$firstFixtureData, 'first fixture data should not be loaded');
    }

    public function testNothingToUnloadParticularExceptOnes()
    {
        $this->_fixtureController->actionUnload('First', '-First');

        $this->assertEmpty(FixtureStorage::$firstFixtureData, 'first fixture data should not be loaded');
    }

    public function testAppendFixtureData()
    {
        $this->assertEmpty(FixtureStorage::$firstFixtureData, 'first fixture data should be unloaded');

        $this->_fixtureController->actionLoad('First');

        $this->assertCount(1, FixtureStorage::$firstFixtureData, 'first fixture data should be loaded');

        $this->_fixtureController->append = true;
        $this->_fixtureController->actionLoad('First');

        $this->assertCount(2, FixtureStorage::$firstFixtureData, 'first fixture data should be appended to already existed one');
    }

    /**
     * @expectedException \yii\console\Exception
     */
    public function testNoFixturesWereFoundInLoad()
    {
        $this->_fixtureController->actionLoad('NotExistingFixture');
    }

    /**
     * @expectedException \yii\console\Exception
     */
    public function testNoFixturesWereFoundInUnload()
    {
        $this->_fixtureController->actionUnload('NotExistingFixture');
    }

}

class FixtureConsoledController extends FixtureController
{

    public function stdout($string)
    {
    }

}
