<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\oci;

use PHPUnit\Framework\Attributes\Group;
use yiiunit\base\db\BaseQueryBuilderUnion;

/**
 * Unit tests for {@see \yii\db\oci\QueryBuilder} UNION and WITH query building tests for the Oracle driver.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('db')]
#[Group('oci')]
#[Group('querybuilder')]
class QueryBuilderUnionTest extends BaseQueryBuilderUnion
{
    protected $driverName = 'oci';
    protected static string $driverNameStatic = 'oci';
}
