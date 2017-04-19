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
        $imagickDriver = new ImagickDriver();
        if (!$imagickDriver->checkRequirements()) {
            static::markTestSkipped($imagickDriver->checkRequirements());
        }

        $imageSettings = new ImageSettings();

        $image = $imagickDriver->renderCaptcha('test render', $imageSettings);
        $size = getimagesizefromstring($image);

        $this->assertEquals($imageSettings->width, $size[0]);
        $this->assertEquals($imageSettings->height, $size[1]);
        $this->assertEquals('image/png', $size['mime']);
    }

    public function testCheckRequirementsAvailableImagickAndAvailablePNG()
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

        $isChecked = $fakeDriver->checkRequirements();
        $this->assertTrue($isChecked);
    }

    public function testCheckRequirementsUnavailableImagickAndAvailablePNG()
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

        $isChecked = $fakeDriver->checkRequirements();
        $this->assertFalse($isChecked);
    }

    public function testCheckRequirementsAvailableImagickAndUnavailablePNG()
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
            ->willReturn(['Unavailable PNG']);

        $isChecked = $fakeDriver->checkRequirements();
        $this->assertFalse($isChecked);
    }
}
