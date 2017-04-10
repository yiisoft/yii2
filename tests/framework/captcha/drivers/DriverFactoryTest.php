<?php

namespace yii\captcha\drivers;

use Yii;
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

    public function testMakeFilledParamValueGD()
    {
        $fakeDriver = $this->getMockBuilder(GdDriver::className())
            ->setMethods(['checkRequirements'])
            ->getMock();
        $fakeDriver->expects($this->any())
            ->method('checkRequirements')
            ->willReturn(true);

        Yii::$container->set(GdDriver::className(), $fakeDriver);

        $factory = new DriverFactory();
        $driverCaptcha = $factory->make(DriverFactory::GD);

        $this->assertInstanceOf(GdDriver::className(), $driverCaptcha);
    }

    public function testMakeFilledParamValueOtherLibrary()
    {
        $this->setExpectedException('\yii\base\InvalidConfigException');

        $factory = new DriverFactory();
        $factory->make('Image other library');
    }

    public function testMakeNotFilledParamAvailableImagick()
    {
        $fakeDriver = $this->getMockBuilder(ImagickDriver::className())
            ->setMethods(['checkRequirements'])
            ->getMock();
        $fakeDriver->expects($this->any())
            ->method('checkRequirements')
            ->willReturn(true);
        Yii::$container->set(ImagickDriver::className(), $fakeDriver);

        $factory = new DriverFactory();
        $driverCaptcha = $factory->make();

        $this->assertInstanceOf(ImagickDriver::className(), $driverCaptcha);
    }

    public function testMakeNotFilledParamAvailableGD()
    {   
        $fakeImagickDriver = $this->getMockBuilder(ImagickDriver::className())
            ->setMethods(['checkRequirements'])
            ->getMock();
        $fakeImagickDriver->expects($this->any())
            ->method('checkRequirements')
            ->willReturn(false);
        Yii::$container->set(GdDriver::className(), $fakeImagickDriver);

        $fakeGdDriver = $this->getMockBuilder(GdDriver::className())
            ->setMethods(['checkRequirements'])
            ->getMock();
        $fakeGdDriver->expects($this->any())
            ->method('checkRequirements')
            ->willReturn(true);
        Yii::$container->set(GdDriver::className(), $fakeGdDriver);

        $factory = new DriverFactory();
        $driverCaptcha = $factory->make();

        $this->assertInstanceOf(GdDriver::className(), $driverCaptcha);
    }

    public function testMakeNotFilledParamNotAvailableImageExtentions()
    {
        $fakeImagickDriver = $this->getMockBuilder(ImagickDriver::className())
            ->setMethods(['checkRequirements'])
            ->getMock();
        $fakeImagickDriver->expects($this->any())
            ->method('checkRequirements')
            ->willReturn(false);
        Yii::$container->set(ImagickDriver::className(), $fakeImagickDriver);

        $fakeGdDriver = $this->getMockBuilder(GdDriver::className())
            ->setMethods(['checkRequirements'])
            ->getMock();
        $fakeGdDriver->expects($this->any())
            ->method('checkRequirements')
            ->willReturn(false);
        Yii::$container->set(GdDriver::className(), $fakeGdDriver);

        $this->setExpectedException('\yii\base\InvalidConfigException');
        $factory = new DriverFactory();
        $factory->make();
    }
}
