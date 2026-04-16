<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql;

use PHPUnit\Framework\Attributes\Group;
use yiiunit\base\db\BaseQueryBuilderUnion;

/**
 * Unit tests for {@see \yii\db\mssql\QueryBuilder} UNION and WITH query building tests for the MSSQL driver.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('db')]
#[Group('mssql')]
#[Group('querybuilder')]
class QueryBuilderUnionTest extends BaseQueryBuilderUnion
{
    protected $driverName = 'sqlsrv';
    protected static string $driverNameStatic = 'sqlsrv';
}
