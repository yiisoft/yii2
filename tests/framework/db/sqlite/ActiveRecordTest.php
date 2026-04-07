<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\sqlite;

use yiiunit\base\db\BaseActiveRecord;

/**
 * @group db
 * @group sqlite
 */
class ActiveRecordTest extends BaseActiveRecord
{
    protected $driverName = 'sqlite';
    protected static string $driverNameStatic = 'sqlite';
}
