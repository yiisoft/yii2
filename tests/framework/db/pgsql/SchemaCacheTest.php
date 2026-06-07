<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\pgsql;

use PHPUnit\Framework\Attributes\Group;
use yiiunit\base\db\BaseSchemaCache;

/**
 * Unit tests for {@see \yii\db\pgsql\Schema} metadata caching and refresh for the PostgreSQL driver.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('db')]
#[Group('pgsql')]
#[Group('schema')]
final class SchemaCacheTest extends BaseSchemaCache
{
    public $driverName = 'pgsql';
}
