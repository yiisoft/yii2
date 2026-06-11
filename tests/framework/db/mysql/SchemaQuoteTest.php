<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mysql;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use yiiunit\base\db\BaseSchemaQuote;
use yiiunit\framework\db\mysql\providers\SchemaProvider;

/**
 * Unit tests for {@see \yii\db\mysql\Schema} identifier and value quoting for the MySQL driver.
 *
 * {@see SchemaProvider} for test case data providers.
 */
#[Group('db')]
#[Group('mysql')]
#[Group('schema')]
#[Group('quote')]
final class SchemaQuoteTest extends BaseSchemaQuote
{
    protected $driverName = 'mysql';
    protected static string $driverNameStatic = 'mysql';

    #[DataProviderExternal(SchemaProvider::class, 'quoteSimpleTableName')]
    public function testQuoteSimpleTableName(string $name, string $expectedName): void
    {
        parent::testQuoteSimpleTableName($name, $expectedName);
    }

    #[DataProviderExternal(SchemaProvider::class, 'quoteValue')]
    public function testQuoteValueQuotesString(string $value, string $expectedValue): void
    {
        parent::testQuoteValueQuotesString($value, $expectedValue);
    }
}
