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
        if (!$gdDriver->checkRequirements()) {
            static::markTestSkipped($gdDriver->getError());
        }

        $imageSettings = new ImageSettings();

        $image = $gdDriver->renderCaptcha('test render', $imageSettings);
        $size = getimagesizefromstring($image);

        $this->assertEquals($imageSettings->width, $size[0]);
        $this->assertEquals($imageSettings->height, $size[1]);
        $this->assertEquals('image/png', $size['mime']);
    }

    public function testCheckRequirementsAvailableGdAndFreeTypeSupport()
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

        $isChecked = $fakeDriver->checkRequirements();
        $this->assertTrue($isChecked);
    }

    public function testCheckRequirementsUnavailableGdAndFreeTypeSupport()
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

        $isChecked = $fakeDriver->checkRequirements();
        $this->assertFalse($isChecked);
    }

    public function testCheckRequirementsAvailableGdAndFreeTypeNotSupport()
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
            ->willReturn(['FreeType Not Support']);

        $isChecked = $fakeDriver->checkRequirements();
        $this->assertFalse($isChecked);
    }
}
