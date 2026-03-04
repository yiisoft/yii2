<?php

namespace yiiunit\framework\helpers\enums;

enum IntBackedEnum: int
{
    case Active = 1;
    case Inactive = 0;
}
