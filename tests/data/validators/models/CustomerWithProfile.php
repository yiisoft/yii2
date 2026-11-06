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
 * Customer whose default query eager-loads the `profile` relation through `with()`.
 */
class CustomerWithProfile extends Customer
{
    public static function find()
    {
        return parent::find()->with('profile');
    }
}
