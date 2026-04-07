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
 * @group pgsql
 * @group log
 */
class PgSQLTargetTest extends BaseDbTarget
{
    protected static $driverName = 'pgsql';
}
