<?php

/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\test;

use yiiunit\TestCase;
use yii\test\ArrayFixture;

class ArrayFixtureTest extends TestCase
{

    /**
     * @var \yii\test\ArrayFixture
     */
    private $_fixture;

    protected function setUp()
    {
        parent::setUp();
        $this->_fixture = new ArrayFixture();
    }

    public function testLoadUnloadParticularFile()
    {
        $this->_fixture->dataFile = '@yiiunit/framework/test/data/array_fixture.php';
        $this->assertEmpty($this->_fixture->data, 'fixture data should be empty');

        $this->_fixture->load();

        $this->assertCount(2, $this->_fixture->data, 'fixture data should match needed total count');
        $this->assertEquals('customer1', $this->_fixture['customer1']['name'], 'first fixture data should match');
        $this->assertEquals('customer2@example.com', $this->_fixture['customer2']['email'], 'second fixture data should match');
    }

    public function testNothingToLoad()
    {
        $this->_fixture->dataFile = false;
        $this->assertEmpty($this->_fixture->data, 'fixture data should be empty');

        $this->_fixture->load();
        $this->assertEmpty($this->_fixture->data, 'fixture data should not be loaded');
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     */
    public function testWrongDataFileException()
    {
        $this->_fixture->dataFile = 'wrong/fixtures/data/path/alias';
        $this->_fixture->load();
    }

}
