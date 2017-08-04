<?php

namespace yiiunit\framework\base;


use yii\base\Object;

class BCObject extends \yii\base\Object
{
    public static $initCalled = false;

    public function __construct($config = [])
    {
        Object::__construct($config);
    }

    public function init()
    {
        static::$initCalled = true;
    }
}