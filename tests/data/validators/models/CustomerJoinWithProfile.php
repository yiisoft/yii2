<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\data\validators\models;

use yiiunit\data\ar\Customer;

/**
 * Customer whose default query joins the `profile` relation through `joinWith()`.
 */
class CustomerJoinWithProfile extends Customer
{
    public static function find()
    {
        return parent::find()->joinWith('profile');
    }
}
