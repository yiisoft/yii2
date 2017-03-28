<?php

namespace yii\captcha\drivers;

use yiiunit\TestCase;
use yii\captcha\drivers\GdDriver;
use yii\captcha\drivers\ImageSettings;
use yii\captcha\drivers\DriverFactory;

class GdDriverTest extends TestCase
{

    public function testGetName()
    {
        $driver = new GdDriver();

        $actualDriverName = $driver->getName();

        $this->assertEquals(DriverFactory::GD, $actualDriverName);
    }

    public function testCheckRequirementsFreeTypeSupport()
    {
        /* @var $driver PHPUnit_Framework_MockObject_MockObject|GdDriver */
        $driver = $this->getMockBuilder(GdDriver::className())
            ->setMethods(['getGDInfo'])
            ->getMock();
        $driver->expects($this->any())
            ->method('getGDInfo')
            ->willReturn(['FreeType Support']);

        $isChecked = $driver->checkRequirements();

        $this->assertTrue($isChecked);
    }

    public function testCheckRequirementsFreeTypeNotSupport()
    {
        /* @var $driver PHPUnit_Framework_MockObject_MockObject|GdDriver */
        $driver = $this->getMockBuilder(GdDriver::className())
            ->setMethods(['getGDInfo'])
            ->getMock();
        $driver->expects($this->any())
            ->method('getGDInfo')
            ->willReturn(['FreeType Not Support']);

        $isChecked = $driver->checkRequirements();

        $this->assertFalse($isChecked);
        $this->assertNotEmpty($driver->getErrors());
    }

    public function testRenderCaptcha()
    {
        $gdDriver = new GdDriver();
        $imageSettings = new ImageSettings();

        $image = $gdDriver->renderCaptcha('test render', $imageSettings);
        $size = getimagesizefromstring($image);

        $this->assertEquals($imageSettings->width, $size[0]);
        $this->assertEquals($imageSettings->height, $size[1]);
        $this->assertEquals('image/png', $size['mime']);
    }
}
