<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\log;

use yiiunit\base\log\BaseDbTarget;

/**
 * @group db
 * @group mysql
 * @group log
 */
class MySQLTargetTest extends BaseDbTarget
{
    protected static $driverName = 'mysql';
}
