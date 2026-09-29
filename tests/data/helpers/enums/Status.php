<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\helpers\enums;

/**
 * Pure enum used to test the enum support of `yii\helpers\ArrayHelper::toArray()`.
 *
 * @see \yiiunit\framework\helpers\ArrayHelperTest
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 */
enum Status
{
    case Active;
    case Inactive;
}
