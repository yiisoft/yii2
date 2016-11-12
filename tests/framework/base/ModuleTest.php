<?php

namespace yiiunit\framework\base;

use yiiunit\TestCase;

/**
 * @group base
 */
class ModuleTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        $this->mockApplication();
    }

    public function testControllerPath()
    {
        $module = new TestModule('test');
        $this->assertEquals('yiiunit\framework\base\controllers', $module->controllerNamespace);
        $this->assertEquals(__DIR__ . DIRECTORY_SEPARATOR . 'controllers', str_replace(['/','\\'], DIRECTORY_SEPARATOR , $module->controllerPath));
    }

    public function testSetupVersion()
    {
        $module = new TestModule('test');

        $version = '1.0.1';
        $module->setVersion($version);
        $this->assertEquals($version, $module->getVersion());

        $module->setVersion(function($module) {
            /* @var $module TestModule */
            return 'version.' . $module->getUniqueId();
        });
        $this->assertEquals('version.test', $module->getVersion());
    }

    /**
     * @depends testSetupVersion
     */
    public function testDefaultVersion()
    {
        $module = new TestModule('test');

        $version = $module->getVersion();
        $this->assertEquals('1.0', $version);
    }
}

class TestModule extends \yii\base\Module
{

}