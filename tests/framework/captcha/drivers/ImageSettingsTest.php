<?php

namespace yii\captcha\drivers;

use yiiunit\TestCase;
use yii\captcha\drivers\ImageSettings;

class ImageSettingsTest extends TestCase
{
    public function testInitNotKnownFontFileName()
    {
        $this->setExpectedException('yii\base\InvalidConfigException');
        new ImageSettings(['fontFile' => '@yii/captcha/Not_known_font.ttf']);
    }
}
