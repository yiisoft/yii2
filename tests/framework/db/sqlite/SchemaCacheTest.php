<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\sqlite;

use PHPUnit\Framework\Attributes\Group;
use yiiunit\base\db\BaseSchemaCache;

/**
 * Unit tests for {@see \yii\db\sqlite\Schema} metadata caching and refresh for the SQLite driver.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('db')]
#[Group('sqlite')]
#[Group('schema')]
final class SchemaCacheTest extends BaseSchemaCache
{
    protected $driverName = 'sqlite';
}
