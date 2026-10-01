<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\enums;

/**
 * Shares its backing values with [[StatusEnum]] on purpose: a case of this enum must never match a
 * [[StatusEnum]] case, even though their backing values are equal.
 */
enum PriorityEnum: int
{
    case LOW = 1;
    case HIGH = 2;
}
