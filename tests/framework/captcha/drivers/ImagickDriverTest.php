<?php

namespace yii\captcha\drivers;

use yiiunit\TestCase;
use yii\captcha\drivers\ImagickDriver;
use yii\captcha\drivers\ImageSettings;

class ImagickDriverTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->mockApplication();
    }

    public function testRenderCaptcha()
    {
        if (!extension_loaded('imagick')) {
            static::markTestSkipped('Imagick extensions are required.');
        }

        $imagickDriver = new ImagickDriver();
        $imageSettings = new ImageSettings();

        $image = $imagickDriver->renderCaptcha('test render', $imageSettings);
        $size = getimagesizefromstring($image);

        $this->assertEquals($imageSettings->width, $size[0]);
        $this->assertEquals($imageSettings->height, $size[1]);
        $this->assertEquals('image/png', $size['mime']);
    }

    public function testGetErrorAvailableImagickAndAvailablePNG()
    {
        /* @var $fakeDriver PHPUnit_Framework_MockObject_MockObject|ImagickDriver */
        $fakeDriver = $this->getMockBuilder(ImagickDriver::className())
            ->setMethods(['isAvailableImagick', 'getImagickFormats'])
            ->getMock();
        $fakeDriver->expects(self::any())
            ->method('isAvailableImagick')
            ->willReturn(true);
        $fakeDriver->expects(self::any())
            ->method('getImagickFormats')
            ->willReturn(['PNG']);

        $error = $fakeDriver->getError();
        $this->assertNull($error);
    }

    public function testGetErrorUnavailableImagickAndAvailablePNG()
    {
        /* @var $fakeDriver PHPUnit_Framework_MockObject_MockObject|ImagickDriver */
        $fakeDriver = $this->getMockBuilder(ImagickDriver::className())
            ->setMethods(['isAvailableImagick', 'getImagickFormats'])
            ->getMock();
        $fakeDriver->expects(self::any())
            ->method('isAvailableImagick')
            ->willReturn(false);
        $fakeDriver->expects(self::any())
            ->method('getImagickFormats')
            ->willReturn(['PNG']);

        $error = $fakeDriver->getError();
        $this->assertEquals('Not available ImageMagick  extension or ImageMagick extension without PNG support is required', $error);
    }

    public function testGetErrorAvailableImagickAndUnavailablePNG()
    {
        /* @var $fakeDriver PHPUnit_Framework_MockObject_MockObject|ImagickDriver */
        $fakeDriver = $this->getMockBuilder(ImagickDriver::className())
            ->setMethods(['isAvailableImagick', 'getImagickFormats'])
            ->getMock();
        $fakeDriver->expects(self::any())
            ->method('isAvailableImagick')
            ->willReturn(false);
        $fakeDriver->expects(self::any())
            ->method('getImagickFormats')
            ->willReturn(['Unavailable PNG']);

        $error = $fakeDriver->getError();
        $this->assertEquals('Not available ImageMagick  extension or ImageMagick extension without PNG support is required', $error);
    }
}
