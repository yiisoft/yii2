<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\enums;

enum StatusEnum: int
{
    case ACTIVE = 1;
    case DELETED = 2;
    case INACTIVE = 0;
}
