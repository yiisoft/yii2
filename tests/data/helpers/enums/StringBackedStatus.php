<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\helpers\enums;

/**
 * String-backed enum used to test the enum support of `yii\helpers\ArrayHelper::toArray()`.
 *
 * @see \yiiunit\framework\helpers\ArrayHelperTest
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 */
enum StringBackedStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
