<?php

namespace yii\captcha\drivers;

use yiiunit\TestCase;
use yii\captcha\drivers\GdDriver;
use yii\captcha\drivers\ImageSettings;

class GdDriverTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->mockApplication();
    }

    public function testRenderCaptcha()
    {
        $gdDriver = new GdDriver();
        if ($error = $gdDriver->getError()) {
            static::markTestSkipped($error);
        }

        $imageSettings = new ImageSettings();

        $image = $gdDriver->renderCaptcha('test render', $imageSettings);
        $size = getimagesizefromstring($image);

        $this->assertEquals($imageSettings->width, $size[0]);
        $this->assertEquals($imageSettings->height, $size[1]);
        $this->assertEquals('image/png', $size['mime']);
    }

    public function testGetErrorAvailableGdAndFreeTypeSupport()
    {
        /* @var $fakeDriver PHPUnit_Framework_MockObject_MockObject|GdDriver */
        $fakeDriver = $this->getMockBuilder(GdDriver::className())
            ->setMethods(['isAvailableGd', 'getGDInfo'])
            ->getMock();
        $fakeDriver->expects(self::any())
            ->method('isAvailableGd')
            ->willReturn(true);
        $fakeDriver->expects(self::any())
            ->method('getGDInfo')
            ->willReturn(['FreeType Support']);

        $error = $fakeDriver->getError();
        $this->assertNull($error);
    }

    public function testGetErrorUnavailableGdAndFreeTypeSupport()
    {
        /* @var $fakeDriver PHPUnit_Framework_MockObject_MockObject|GdDriver */
        $fakeDriver = $this->getMockBuilder(GdDriver::className())
            ->setMethods(['isAvailableGd', 'getGDInfo'])
            ->getMock();
        $fakeDriver->expects(self::any())
            ->method('isAvailableGd')
            ->willReturn(false);
        $fakeDriver->expects(self::any())
            ->method('getGDInfo')
            ->willReturn(['FreeType Support']);

        $error = $fakeDriver->getError();
        $this->assertEquals('Not available GD  extension or either GD without FreeType support', $error);
    }

    public function testGetErrorAvailableGdAndFreeTypeNotSupport()
    {
        /* @var $fakeDriver PHPUnit_Framework_MockObject_MockObject|GdDriver */
        $fakeDriver = $this->getMockBuilder(GdDriver::className())
            ->setMethods(['isAvailableGd', 'getGDInfo'])
            ->getMock();
        $fakeDriver->expects(self::any())
            ->method('isAvailableGd')
            ->willReturn(false);
        $fakeDriver->expects(self::any())
            ->method('getGDInfo')
            ->willReturn(['FreeType Not Support']);

        $error = $fakeDriver->getError();
        $this->assertEquals('Not available GD  extension or either GD without FreeType support', $error);
    }
}
