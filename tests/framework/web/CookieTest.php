<?php

namespace yiiunit\framework\web;

use yiiunit\TestCase;
use yii\web\Cookie;

class CookieTest extends TestCase
{
    public function testSetName_nameEqualsDots_expectInvalidParamException()
    {
        $this->expectException('\yii\base\InvalidParamException');
        new Cookie(['name' => 'my.name']);
    }

    public function testSetName_nameNotEqualsDots_nameFilled()
    {
        $cookie = new Cookie(['name' => 'my_name']);

        $this->assertEquals('my_name', $cookie->name);
    }
}
