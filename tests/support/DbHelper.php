<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\support;

use PHPUnit\Framework\Assert;
use yii\db\Connection;
use yii\db\ConstraintFinderInterface;

use function str_contains;

/**
 * Common database utilities for test support code.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class DbHelper
{
    /**
     * Asserts the refreshed abstract type of the given column matches the expected value.
     *
     * @param Connection $db Database connection.
     * @param string $table Table name.
     * @param string $column Column name.
     * @param string $type Expected abstract type.
     */
    public static function assertColumnType(Connection $db, string $table, string $column, string $type): void
    {
        Assert::assertSame(
            $type,
            $db->getTableSchema($table, true)->getColumn($column)->type,
            'Abstract type must match.',
        );
    }

    /**
     * Asserts the refreshed physical type of the given column matches the expected value.
     *
     * @param Connection $db Database connection.
     * @param string $table Table name.
     * @param string $column Column name.
     * @param string $dbType Expected physical type.
     */
    public static function assertColumnDbType(Connection $db, string $table, string $column, string $dbType): void
    {
        Assert::assertSame(
            $dbType,
            $db->getTableSchema($table, true)->getColumn($column)->dbType,
            'Physical type must match.',
        );
    }

    /**
     * Asserts the refreshed nullability of the given column matches the expected value.
     *
     * @param Connection $db Database connection.
     * @param string $table Table name.
     * @param string $column Column name.
     * @param bool $allowNull Expected nullability.
     */
    public static function assertColumnAllowNull(Connection $db, string $table, string $column, bool $allowNull): void
    {
        Assert::assertSame(
            $allowNull,
            $db->getTableSchema($table, true)->getColumn($column)->allowNull,
            'Nullability must match.',
        );
    }

    /**
     * Asserts the refreshed default value of the given column matches the expected value.
     *
     * @param Connection $db Database connection.
     * @param string $table Table name.
     * @param string $column Column name.
     * @param mixed $defaultValue Expected default value, or `null` when the default must be cleared.
     */
    public static function assertColumnDefaultValue(
        Connection $db,
        string $table,
        string $column,
        mixed $defaultValue,
    ): void {
        Assert::assertSame(
            $defaultValue,
            $db->getTableSchema($table, true)->getColumn($column)->defaultValue,
            $defaultValue === null ? 'Default must be cleared.' : 'Default must match.',
        );
    }

    /**
     * Asserts the refreshed default value of the given column is present and contains the given substring.
     *
     * @param Connection $db Database connection.
     * @param string $table Table name.
     * @param string $column Column name.
     * @param string $needle Substring the default value must contain.
     */
    public static function assertColumnDefaultValueContains(
        Connection $db,
        string $table,
        string $column,
        string $needle,
    ): void {
        $defaultValue = $db->getTableSchema($table, true)->getColumn($column)->defaultValue;

        Assert::assertNotNull(
            $defaultValue,
            'Default must be present.',
        );
        Assert::assertStringContainsString(
            $needle,
            (string) $defaultValue,
            'Default expression must be preserved.',
        );
    }

    /**
     * Asserts at least one refreshed default value constraint on the table contains the given substring.
     *
     * @param Connection $db Database connection.
     * @param string $table Table name.
     * @param string $needle Substring the default value constraint must contain.
     */
    public static function assertDefaultConstraintContains(Connection $db, string $table, string $needle): void
    {
        $schema = $db->getSchema();

        /** @var ConstraintFinderInterface $schema */
        $found = false;

        foreach ($schema->getTableDefaultValues($table, true) as $default) {
            if (str_contains((string) $default->value, $needle)) {
                $found = true;
            }
        }

        Assert::assertTrue(
            $found,
            'Default expression must be preserved.',
        );
    }

    /**
     * Asserts at least one refreshed check constraint on the table contains the given substring.
     *
     * @param Connection $db Database connection.
     * @param string $table Table name.
     * @param string $needle Substring the check expression must contain.
     */
    public static function assertCheckConstraintContains(Connection $db, string $table, string $needle): void
    {
        $schema = $db->getSchema();

        /** @var ConstraintFinderInterface $schema */
        $found = false;

        foreach ($schema->getTableChecks($table, true) as $check) {
            if (str_contains($check->expression, $needle)) {
                $found = true;
            }
        }

        Assert::assertTrue(
            $found,
            'Check constraint must exist.',
        );
    }

    /**
     * Asserts exactly one refreshed unique constraint on the table covers the given columns.
     *
     * @param Connection $db Database connection.
     * @param string $table Table name.
     * @param array $columns Column names the unique constraint must cover.
     */
    public static function assertSingleUniqueConstraintCovers(Connection $db, string $table, array $columns): void
    {
        $schema = $db->getSchema();

        /** @var ConstraintFinderInterface $schema */
        $matches = 0;

        foreach ($schema->getTableUniques($table, true) as $unique) {
            if ($unique->columnNames === $columns) {
                ++$matches;
            }
        }

        Assert::assertSame(
            1,
            $matches,
            'Exactly one unique constraint must cover the column.',
        );
    }

    /**
     * Asserts the number of refreshed check constraints on the table matches the expected count.
     *
     * @param Connection $db Database connection.
     * @param string $table Table name.
     * @param int $count Expected number of check constraints.
     */
    public static function assertCheckConstraintCount(Connection $db, string $table, int $count): void
    {
        $schema = $db->getSchema();

        /** @var ConstraintFinderInterface $schema */
        Assert::assertCount(
            $count,
            $schema->getTableChecks($table, true),
            'Check must be dropped with the column alteration.',
        );
    }

    /**
     * Asserts the number of refreshed default value constraints on the table matches the expected count.
     *
     * @param Connection $db Database connection.
     * @param string $table Table name.
     * @param int $count Expected number of default value constraints.
     */
    public static function assertDefaultConstraintCount(Connection $db, string $table, int $count): void
    {
        $schema = $db->getSchema();

        /** @var ConstraintFinderInterface $schema */
        Assert::assertCount(
            $count,
            $schema->getTableDefaultValues($table, true),
            'Default must be dropped with the column alteration.',
        );
    }

    /**
     * Asserts the number of refreshed unique constraints on the table matches the expected count.
     *
     * @param Connection $db Database connection.
     * @param string $table Table name.
     * @param int $count Expected number of unique constraints.
     */
    public static function assertUniqueConstraintCount(Connection $db, string $table, int $count): void
    {
        $schema = $db->getSchema();

        /** @var ConstraintFinderInterface $schema */
        Assert::assertCount(
            $count,
            $schema->getTableUniques($table, true),
            'Unique constraint must be dropped with the column alteration.',
        );
    }

    /**
     * Drops the given tables when they exist, skipping the ones that are absent.
     *
     * @param Connection $db Database connection.
     * @param array $tables Table names to drop, in dependency-safe order.
     */
    public static function dropTablesIfExist(Connection $db, array $tables): void
    {
        foreach ($tables as $table) {
            if ($db->getTableSchema($table, true) !== null) {
                $db->createCommand()->dropTable($table)->execute();
            }
        }
    }

    /**
     * Creates the given schema on SQL Server when it does not already exist.
     *
     * Targets the MSSQL driver: binds the name as a parameter and quotes it with `QUOTENAME()`, so schema names
     *
     * @param Connection $db Database connection.
     * @param string $schema Schema name to create.
     */
    public static function createSchemaIfNotExist(Connection $db, string $schema): void
    {
        $sql = <<<SQL
        DECLARE @schema sysname = :schema;

        IF SCHEMA_ID(@schema) IS NULL
        BEGIN
            DECLARE @sql nvarchar(max) = N'CREATE SCHEMA ' + QUOTENAME(@schema);

            EXEC (@sql);
        END
        SQL;

        $db->createCommand($sql, [':schema' => $schema])->execute();
    }

    /**
     * Drops the given schema on SQL Server when it exists.
     *
     * Targets the MSSQL driver: binds the name as a parameter and quotes it with `QUOTENAME()`, so schema names
     * containing reserved characters are handled safely.
     *
     * @param Connection $db Database connection.
     * @param string $schema Schema name to drop.
     */
    public static function dropSchemaIfExist(Connection $db, string $schema): void
    {
        $sql = <<<SQL
        DECLARE @schema sysname = :schema;

        IF SCHEMA_ID(@schema) IS NOT NULL
        BEGIN
            DECLARE @sql nvarchar(max) = N'DROP SCHEMA ' + QUOTENAME(@schema);

            EXEC (@sql);
        END
        SQL;

        $db->createCommand($sql, [':schema' => $schema])->execute();
    }
}
