<?php

namespace yiiunit\framework\di\stubs;

use yii\base\BaseObject;

class UnionTypeWithClass extends BaseObject
{
    public function __construct(public string|Beta $value)
    {
    }
}
