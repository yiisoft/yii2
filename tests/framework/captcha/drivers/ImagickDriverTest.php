<?php

namespace yii\captcha\drivers;

use yiiunit\TestCase;
use yii\captcha\drivers\ImagickDriver;
use yii\captcha\drivers\ImageSettings;
use yii\captcha\drivers\DriverFactory;

class ImagickDriverTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        if (!extension_loaded('imagick')) {
            static::markTestSkipped('Imagick extensions are required.');
        }
    }

    public function testGetName()
    {
        $driver = new ImagickDriver();

        $actualDriverName = $driver->getName();

        $this->assertEquals(DriverFactory::IMAGICK, $actualDriverName);
    }

    public function testCheckRequirementsAvailablePNG()
    {
        /* @var $driver PHPUnit_Framework_MockObject_MockObject|ImagickDriver */
        $driver = $this->getMockBuilder(ImagickDriver::className())
            ->setMethods(['getImagickFormats'])
            ->getMock();
        $driver->expects($this->any())
            ->method('getImagickFormats')
            ->willReturn(['PNG']);

        $isChecked = $driver->checkRequirements();

        $this->assertTrue($isChecked);
    }

    public function testCheckRequirementsNotAvailablePNG()
    {
        /* @var $driver PHPUnit_Framework_MockObject_MockObject|ImagickDriver */
        $driver = $this->getMockBuilder(ImagickDriver::className())
            ->setMethods(['getImagickFormats'])
            ->getMock();
        $driver->expects($this->any())
            ->method('getImagickFormats')
            ->willReturn(['JPEG']);

        $isChecked = $driver->checkRequirements();

        $this->assertFalse($isChecked);
        $this->assertNotEmpty($driver->getErrors());
    }

    /**
     * @group imagick
     */
    public function testRenderCaptcha()
    {
        $imagickDriver = new ImagickDriver();
        $imageSettings = new ImageSettings();

        $image = $imagickDriver->renderCaptcha('test render', $imageSettings);
        $size = getimagesizefromstring($image);

        $this->assertEquals($imageSettings->width, $size[0]);
        $this->assertEquals($imageSettings->height, $size[1]);
        $this->assertEquals('image/png', $size['mime']);
    }
}
