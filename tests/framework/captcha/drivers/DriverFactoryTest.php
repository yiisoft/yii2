<?php

namespace yii\captcha\drivers;

use yiiunit\TestCase;
use yii\captcha\drivers\DriverFactory;
use yii\captcha\drivers\ImagickDriver;
use yii\captcha\drivers\GdDriver;

class DriverFactoryTest extends TestCase
{

    protected function setUp()
    {
        parent::setUp();

        $this->mockApplication();
    }

    public function testMakeNotFilledParam()
    {
        /* @var $factory PHPUnit_Framework_MockObject_MockObject|DriverFactory */
        $factory = $this->getMockBuilder(DriverFactory::className())
            ->setMethods(['getAvailableDriverName'])
            ->getMock();
        $factory->expects($this->any())
            ->method('getAvailableDriverName')
            ->willReturn(DriverFactory::GD);


        $driverCaptcha = $factory->make();

        $this->assertInstanceOf(GdDriver::className(), $driverCaptcha);
    }

    public function testMakeFilledParamValueImagick()
    {
        $factory = new DriverFactory();

        $driverCaptcha = $factory->make(DriverFactory::IMAGICK);

        $this->assertInstanceOf(ImagickDriver::className(), $driverCaptcha);
    }

    public function testMakeFilledParamValueGD()
    {
        $factory = new DriverFactory();

        $driverCaptcha = $factory->make(DriverFactory::GD);

        $this->assertInstanceOf(GdDriver::className(), $driverCaptcha);
    }

    public function testMakeFilledParamValueOtherLibrary()
    {
        $factory = new DriverFactory();

        $this->setExpectedException('\yii\base\InvalidConfigException');
        $factory->make('OtherLibrary');
    }

    /**
     */
    public function testGetAvailableDriverNameLoadedExtensionImagick()
    {
        /* @var $factory PHPUnit_Framework_MockObject_MockObject|DriverFactory */
        $factory = $this->getMockBuilder(DriverFactory::className())
            ->setMethods(['getLoadedExtensions'])
            ->getMock();
        $factory->expects($this->any())
            ->method('getLoadedExtensions')
            ->willReturn([DriverFactory::IMAGICK]);

        $actualDriverName = $this->invokeMethod($factory, 'getAvailableDriverName');

        $this->assertEquals(DriverFactory::IMAGICK, $actualDriverName);
    }

    public function testGetAvailableDriverNameLoadedExtensionGD()
    {
        /* @var $factory PHPUnit_Framework_MockObject_MockObject|DriverFactory */
        $factory = $this->getMockBuilder(DriverFactory::className())
            ->setMethods(['getLoadedExtensions'])
            ->getMock();
        $factory->expects($this->any())
            ->method('getLoadedExtensions')
            ->willReturn([DriverFactory::GD]);

        $actualDriverName = $this->invokeMethod($factory, 'getAvailableDriverName');

        $this->assertEquals(DriverFactory::GD, $actualDriverName);
    }

    /**
     */
    public function testGetAvailableDriverNamePriorityImagick()
    {
        /* @var $factory PHPUnit_Framework_MockObject_MockObject|DriverFactory */
        $factory = $this->getMockBuilder(DriverFactory::className())
            ->setMethods(['getLoadedExtensions'])
            ->getMock();
        $factory->expects($this->any())
            ->method('getLoadedExtensions')
            ->willReturn([DriverFactory::GD, DriverFactory::IMAGICK]);

        $actualDriverName = $this->invokeMethod($factory, 'getAvailableDriverName');

        $this->assertEquals(DriverFactory::IMAGICK, $actualDriverName);
    }

    /**
     *
     */
    public function testGetAvailableDriverNameLoadedOtherImagicLibrary()
    {
        /* @var $factory PHPUnit_Framework_MockObject_MockObject|DriverFactory */
        $factory = $this->getMockBuilder(DriverFactory::className())
            ->setMethods(['getLoadedExtensions'])
            ->getMock();
        $factory->expects($this->any())
            ->method('getLoadedExtensions')
            ->willReturn(['Other imagic library']);

        $actualDriverName = $this->invokeMethod($factory, 'getAvailableDriverName');

        $this->assertNull($actualDriverName);
    }
}
