<?php

namespace yii\captcha;

use Yii;
use yiiunit\TestCase;
use yii\captcha\CaptchaAction;
use yii\captcha\drivers\DriverFactory;
use yii\captcha\drivers\ImagickDriver;
use yii\captcha\drivers\GdDriver;

class CaptchaActionTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->mockApplication();
    }

    public function testRenderImageLibraryEqualsGD()
    {
        $fakeDriver = $this->getMockBuilder(GdDriver::className())
            ->setMethods(['getError', 'renderCaptcha'])
            ->getMock();
        $fakeDriver->expects($this->any())
            ->method('getError')
            ->willReturn(null);
        $fakeDriver->expects($this->any())
            ->method('renderCaptcha')
            ->willReturn('test code');
        Yii::$container->set(GdDriver::className(), $fakeDriver);
        
        $action = new CaptchaAction('generate', 'Captcha');
        $action->imageLibrary = DriverFactory::GD;

        $captchaDriver = $this->invokeMethod($action, 'renderImage', ['code' => 'test code']);

        $this->assertEquals('test code', $captchaDriver);
    }

    public function testRenderImageLibraryEqualsNull()
    {
        $fakeImagickDriver = $this->getMockBuilder(ImagickDriver::className())
            ->setMethods(['getError'])
            ->getMock();
        $fakeImagickDriver->expects($this->any())
            ->method('getError')
            ->willReturn('Imagick not supported');
        Yii::$container->setSingleton(ImagickDriver::className(), $fakeImagickDriver);

        $fakeGdDriver = $this->getMockBuilder(GdDriver::className())
            ->setMethods(['getError', 'renderCaptcha'])
            ->getMock();
        $fakeGdDriver->expects($this->any())
            ->method('getError')
            ->willReturn(null);
        $fakeGdDriver->expects($this->any())
            ->method('renderCaptcha')
            ->willReturn('test code');
        Yii::$container->setSingleton(GdDriver::className(), $fakeGdDriver);

        $action = new CaptchaAction('generate', 'Captcha');

        $captchaDriver = $this->invokeMethod($action, 'renderImage', ['code' => 'test code']);

        $this->assertEquals('test code', $captchaDriver);
    }
}
