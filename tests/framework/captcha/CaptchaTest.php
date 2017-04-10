<?php

namespace yii\captcha;

use Yii;
use yii\captcha\Captcha;
use yiiunit\TestCase;

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
}
