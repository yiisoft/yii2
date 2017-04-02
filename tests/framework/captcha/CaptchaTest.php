<?php

namespace yii\captcha;

use Yii;
use yii\captcha\Captcha;
use yiiunit\TestCase;
use yii\captcha\drivers\GdDriver;

class CaptchaTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->mockApplication();

        $fakeGdDriver = $this->getMockBuilder(GdDriver::className())
            ->setMethods(['getError'])
            ->getMock();
        $fakeGdDriver->expects($this->any())
            ->method('getError')
            ->willReturn(null);
        Yii::$container->setSingleton(GdDriver::className(), $fakeGdDriver);
    }

    /**
     * @covers yii\captcha\Captcha::init
     * @todo   Implement testInit().
     */
    public function testInitFilledOptionsId()
    {
        $initValues = [
            'name'    => 'captcha',
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

    public function testCheckRequirementsNotAvailableSupportedImageLibrary()
    {
        $fakeGdDriver = $this->getMockBuilder(GdDriver::className())
            ->setMethods(['getError'])
            ->getMock();
        $fakeGdDriver->expects($this->any())
            ->method('getError')
            ->willReturn('Error have for GD');
        \Yii::$container->setSingleton(GdDriver::className(), $fakeGdDriver);

        $this->setExpectedException('\yii\base\InvalidConfigException');

        new Captcha();
    }
}
