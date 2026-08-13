<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\log\mssql;

use PHPUnit\Framework\Attributes\Group;
use yiiunit\base\log\BaseDbTarget;

/**
 * Unit test for {@see \yii\log\DbTarget} with MSSQL driver.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('db')]
#[Group('log')]
#[Group('mssql')]
final class DbTargetTest extends BaseDbTarget
{
    /**
     * @var string the driver name of this test class.
     */
    protected $driverName = 'sqlsrv';
}
