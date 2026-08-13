<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\log\sqlite;

use PHPUnit\Framework\Attributes\Group;
use yiiunit\base\log\BaseDbTarget;

use function is_file;
use function unlink;

/**
 * Unit test for {@see \yii\log\DbTarget} with SQLite driver.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('db')]
#[Group('log')]
#[Group('sqlite')]
final class DbTargetTest extends BaseDbTarget
{
    /**
     * @var string the driver name of this test class.
     */
    protected $driverName = 'sqlite';

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        if (is_file(self::SQLITE_DATABASE_FILE)) {
            unlink(self::SQLITE_DATABASE_FILE);
        }
    }
}
