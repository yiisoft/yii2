<?php

namespace yiiunit\framework\di\stubs;

class UnionTypeNotNull
{
    public function __construct(protected string|int|float|bool $value)
    {
    }
}
