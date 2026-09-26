<?php

namespace yiiunit\framework\db\stubs;

enum StringBackedStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}

enum IntBackedStatus: int
{
    case On = 1;
    case Off = 0;
}
