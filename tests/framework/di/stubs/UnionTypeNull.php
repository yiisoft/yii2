<?php

namespace yiiunit\framework\di\stubs;

class UnionTypeNull
{
    public function __construct(protected string|int|float|bool|null $value)
    {
    }
}
