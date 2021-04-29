<?php

namespace yiiunit\framework\di\stubs;

class UnionTypeWithClass
{
    public function __construct(public string|Beta $value)
    {
    }
}
