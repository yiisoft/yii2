<?php

namespace yiiunit\framework\di\stubs;

use yii\base\BaseObject;

class UnionTypeNotNull extends BaseObject
{
    public function __construct(protected string|int|float|bool $value)
    {
    }
}
