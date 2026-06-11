<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\base\db;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use yiiunit\base\db\providers\SchemaProvider;
use yiiunit\framework\db\DatabaseTestCase;

/**
 * Base unit tests for {@see \yii\db\Schema} identifier and value quoting across all database drivers.
 *
 * {@see SchemaProvider} for test case data providers.
 */
abstract class BaseSchemaQuote extends DatabaseTestCase
{
    #[DataProviderExternal(SchemaProvider::class, 'quoteColumnName')]
    public function testQuoteColumnName(string $name, string $expectedName): void
    {
        $schema = $this->getConnection(false, false)->getSchema();

        self::assertSame(
            static::replaceQuotes($expectedName),
            $schema->quoteColumnName(static::replaceQuotes($name)),
            'Quoted column name must match the expected quoting.',
        );
    }

    #[DataProviderExternal(SchemaProvider::class, 'quoteSimpleColumnName')]
    public function testQuoteSimpleColumnName(string $name, string $expectedName): void
    {
        $schema = $this->getConnection(false, false)->getSchema();

        self::assertSame(
            static::replaceQuotes($expectedName),
            $schema->quoteSimpleColumnName(static::replaceQuotes($name)),
            'Quoted simple column name must match the expected quoting.',
        );
    }

    #[DataProviderExternal(SchemaProvider::class, 'quoteSimpleTableName')]
    public function testQuoteSimpleTableName(string $name, string $expectedName): void
    {
        $schema = $this->getConnection(false, false)->getSchema();

        self::assertSame(
            static::replaceQuotes($expectedName),
            $schema->quoteSimpleTableName(static::replaceQuotes($name)),
            'Quoted simple table name must match the expected quoting.',
        );
    }

    #[DataProviderExternal(SchemaProvider::class, 'quoteTableName')]
    public function testQuoteTableName(string $name, string $expectedName): void
    {
        $schema = $this->getConnection(false, false)->getSchema();

        self::assertSame(
            static::replaceQuotes($expectedName),
            $schema->quoteTableName(static::replaceQuotes($name)),
            'Quoted table name must match the expected quoting.',
        );
    }

    #[DataProviderExternal(SchemaProvider::class, 'quoteValue')]
    public function testQuoteValueQuotesString(string $value, string $expectedValue): void
    {
        $schema = $this->getConnection(false)->getSchema();

        self::assertSame(
            $expectedValue,
            $schema->quoteValue($value),
            'String value must be quoted and escaped for the driver.',
        );
    }

    #[DataProviderExternal(SchemaProvider::class, 'quoteValueNotString')]
    public function testQuoteValueReturnsNonStringValueUnchanged(mixed $value): void
    {
        $schema = $this->getConnection(false, false)->getSchema();

        self::assertSame(
            $value,
            $schema->quoteValue($value),
            'Non-string value must be returned unchanged.',
        );
    }

    #[DataProviderExternal(SchemaProvider::class, 'unquoteSimpleColumnName')]
    public function testUnquoteSimpleColumnName(string $name, string $expectedName): void
    {
        $schema = $this->getConnection(false, false)->getSchema();

        self::assertSame(
            static::replaceQuotes($expectedName),
            $schema->unquoteSimpleColumnName(static::replaceQuotes($name)),
            'Unquoted simple column name must match the expected name.',
        );
    }

    #[DataProviderExternal(SchemaProvider::class, 'unquoteSimpleTableName')]
    public function testUnquoteSimpleTableName(string $name, string $expectedName): void
    {
        $schema = $this->getConnection(false, false)->getSchema();

        self::assertSame(
            static::replaceQuotes($expectedName),
            $schema->unquoteSimpleTableName(static::replaceQuotes($name)),
            'Unquoted simple table name must match the expected name.',
        );
    }
}
