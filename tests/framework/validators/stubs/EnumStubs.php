<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\validators\stubs;

enum StringStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}

enum IntStatus: int
{
    case On = 1;
    case Off = 0;
}

enum Suit
{
    case Hearts;
    case Diamonds;
    case Clubs;
    case Spades;
}
