<?php

namespace yii\captcha;

use yii\captcha\Captcha;
use yiiunit\TestCase;
use yii\captcha\drivers\DriverFactory;
use yii\captcha\drivers\GdDriver;

class CaptchaTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->mockApplication();
    }

    /**
     * @covers yii\captcha\Captcha::init
     * @todo   Implement testInit().
     */
    public function testInitFilledOptionsId()
    {
        $initValues = [
            'name' => 'captcha',
            'options' => [
                'id' => 'captcha-id'
            ]
        ];
        $captcha = new Captcha($initValues);

        $this->assertEquals('captcha-id-image', $captcha->imageOptions['id']);
    }

    /**
     * @covers yii\captcha\Captcha::init
     * @todo   Implement testInit().
     */
    public function testInitNotFilledOptionsId()
    {
        $initValues = ['name' => 'captcha'];
        $captcha = new Captcha($initValues);

        $this->assertStringEndsWith('-image', $captcha->imageOptions['id']);
    }

    public function testCheckRequirementsAvailableSupportedImageLibrary()
    {
        $fakeDriverFactory = $this->getMockBuilder(DriverFactory::className())
            ->setMethods(['make'])
            ->getMock();

        $gdDriver = $this->getMockBuilder(GdDriver::className())
            ->setMethods(['getGDInfo'])
            ->getMock();
        $gdDriver->expects(self::any())
            ->method('getGDInfo')
            ->willReturn(['FreeType Support']);

        $fakeDriverFactory->expects($this->any())
            ->method('make')
            ->willReturn($gdDriver);
        \Yii::$container->set(DriverFactory::className(), $fakeDriverFactory);

        $actualDriverName = Captcha::checkRequirements();

        $this->assertEquals(DriverFactory::GD, $actualDriverName);
    }

    public function testCheckRequirementsImageLibraryNotCheckRequirements()
    {
        $fakeDriverFactory = $this->getMockBuilder(DriverFactory::className())
            ->setMethods(['make'])
            ->getMock();

        $gdDriver = $this->getMockBuilder(GdDriver::className())
            ->setMethods(['getGDInfo'])
            ->getMock();
        $gdDriver->expects(self::any())
            ->method('getGDInfo')
            ->willReturn([]);

        $fakeDriverFactory->expects($this->any())
            ->method('make')
            ->willReturn($gdDriver);
        \Yii::$container->set(DriverFactory::className(), $fakeDriverFactory);

        $this->setExpectedException('\yii\base\InvalidConfigException');
        Captcha::checkRequirements();
    }
}
