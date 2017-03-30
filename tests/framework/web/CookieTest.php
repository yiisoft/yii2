<?php

namespace yiiunit\framework\web;

use Yii;
use yii\caching\FileCache;
use yii\web\CacheSession;
use yii\web\Cookie;

/**
 * @group web
 */
class CookieTest extends \yiiunit\TestCase
{
    public function testSerializeUnserialize()
    {
        $cookie = new Cookie();
        $cookie->name = "test";
        $cookie->value = [
            'a' => 'b',
            'c' => 5
        ];

        $result = Cookie::fromDataString($cookie->toDataString());
        $this->assertInstanceOf(Cookie::className(), $result);
        $this->assertEquals($cookie->value, $result->value);
        $this->assertEquals($cookie->name, $result->name);

    }

}
