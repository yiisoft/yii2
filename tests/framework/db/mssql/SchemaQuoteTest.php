<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use yiiunit\base\db\BaseSchemaQuote;
use yiiunit\framework\db\mssql\providers\SchemaProvider;

/**
 * Unit tests for {@see \yii\db\mssql\Schema::quoteTableName()} and {@see \yii\db\mssql\Schema::quoteColumnName()}
 * identifier quoting for the MSSQL driver.
 *
 * {@see SchemaProvider} for test case data providers.
 */
#[Group('db')]
#[Group('mssql')]
#[Group('schema')]
#[Group('quote')]
final class SchemaQuoteTest extends BaseSchemaQuote
{
    protected $driverName = 'sqlsrv';
    protected static string $driverNameStatic = 'sqlsrv';

    #[DataProviderExternal(SchemaProvider::class, 'quoteColumnName')]
    public function testQuoteColumnName(string $name, string $expectedName): void
    {
        parent::testQuoteColumnName($name, $expectedName);
    }

    #[DataProviderExternal(SchemaProvider::class, 'quoteSimpleTableName')]
    public function testQuoteSimpleTableName(string $name, string $expectedName): void
    {
        parent::testQuoteSimpleTableName($name, $expectedName);
    }

    #[DataProviderExternal(SchemaProvider::class, 'quoteTableName')]
    public function testQuoteTableName(string $name, string $expectedName): void
    {
        parent::testQuoteTableName($name, $expectedName);
    }
}
