<?php

namespace yiiunit\framework\di\stubs;

use yii\base\BaseObject;

class Car extends BaseObject
{
    public function __construct(public $color, public $name)
    {
    }
}
