<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mysql;

use yiiunit\base\validators\BaseUniqueValidator;

/**
 * @group db
 * @group mysql
 * @group validators
 */
class UniqueValidatorTest extends BaseUniqueValidator
{
    public $driverName = 'mysql';
}
