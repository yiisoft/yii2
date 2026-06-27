<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\support;

use yii\db\Connection;

/**
 * Common database utilities for test support code.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
final class DbHelper
{
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
     * Targets the MSSQL driver through `SCHEMA_ID()` and a batched `EXEC('CREATE SCHEMA ...')` statement.
     *
     * @param Connection $db Database connection.
     * @param string $schema Schema name to create.
     */
    public static function createSchemaIfNotExist(Connection $db, string $schema): void
    {
        $sql = <<<SQL
        IF SCHEMA_ID('$schema') IS NULL EXEC('CREATE SCHEMA [$schema]')
        SQL;

        $db->createCommand($sql)->execute();
    }

    /**
     * Drops the given schema on SQL Server when it exists.
     *
     * Targets the MSSQL driver through `SCHEMA_ID()` and a batched `EXEC('DROP SCHEMA ...')` statement.
     *
     * @param Connection $db Database connection.
     * @param string $schema Schema name to drop.
     */
    public static function dropSchemaIfExist(Connection $db, string $schema): void
    {
        $sql = <<<SQL
        IF SCHEMA_ID('$schema') IS NOT NULL EXEC('DROP SCHEMA [$schema]')
        SQL;

        $db->createCommand($sql)->execute();
    }
}
