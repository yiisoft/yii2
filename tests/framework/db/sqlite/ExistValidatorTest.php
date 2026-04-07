<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\sqlite;

use yiiunit\base\validators\BaseExistValidator;

/**
 * @group db
 * @group sqlite
 * @group validators
 */
class ExistValidatorTest extends BaseExistValidator
{
    public $driverName = 'sqlite';
}
