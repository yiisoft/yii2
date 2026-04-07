<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\oci;

use yiiunit\base\validators\BaseExistValidator;

/**
 * @group db
 * @group oci
 * @group validators
 */
class ExistValidatorTest extends BaseExistValidator
{
    public $driverName = 'oci';
}
