<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\base\db;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Depends;
use yii\caching\ArrayCache;
use yii\caching\FileCache;
use yii\db\TableSchema;
use yiiunit\base\db\providers\SchemaCacheProvider;
use yiiunit\framework\db\DatabaseTestCase;

/**
 * Base unit tests for {@see \yii\db\Schema} metadata caching and refresh across all database drivers.
 *
 * {@see SchemaCacheProvider} for test case data providers.
 */
abstract class BaseSchemaCache extends DatabaseTestCase
{
    public function testRefresh(): void
    {
        $schema = $this->getConnection()->getSchema();

        $schema->db->enableSchemaCache = true;
        $schema->db->schemaCache = new ArrayCache();

        $tableBeforeRefresh = $schema->getTableSchema('type');

        self::assertInstanceOf(
            TableSchema::class,
            $tableBeforeRefresh,
            'Table schema should be available before refresh.',
        );

        $schema->refresh();

        $tableAfterRefresh = $schema->getTableSchema('type');

        self::assertInstanceOf(
            TableSchema::class,
            $tableAfterRefresh,
            'Table schema should be available after refresh.',
        );
        self::assertNotSame(
            $tableBeforeRefresh,
            $tableAfterRefresh,
            'Table schema should be reloaded after refresh.',
        );
    }

    #[Depends('testSchemaCache')]
    public function testRefreshTableSchema(): void
    {
        $schema = $this->getConnection()->getSchema();

        $schema->db->enableSchemaCache = true;
        $schema->db->schemaCache = new FileCache();

        $noCacheTable = $schema->getTableSchema('type', true);

        $schema->refreshTableSchema('type');

        $refreshedTable = $schema->getTableSchema('type', false);

        self::assertNotSame(
            $noCacheTable,
            $refreshedTable,
            'Refreshing table should return a different instance.',
        );
    }

    public function testSchemaCache(): void
    {
        $db = $this->getConnection();

        $schema = $db->getSchema();

        $schema->db->enableSchemaCache = true;
        $schema->db->schemaCache = new FileCache();

        $noCacheTable = $schema->getTableSchema('type', true);
        $cachedTable = $schema->getTableSchema('type', false);

        self::assertSame(
            $noCacheTable,
            $cachedTable,
            'Getting table with cache should return the same instance.',
        );

        $db->createCommand()->renameTable('type', 'type_test');

        $noCacheTable = $schema->getTableSchema('type', true);

        self::assertNotSame(
            $noCacheTable,
            $cachedTable,
            'Getting table with cache should return a different instance.',
        );

        $db->createCommand()->renameTable('type_test', 'type');
    }

    public function testSchemaCacheExtreme(): void
    {
        $db = $this->getConnection();

        $command = $db->createCommand();
        $schema = $db->getSchema();

        $schema->db->enableSchemaCache = true;
        $schema->db->schemaCache = new ArrayCache();

        if ($schema->getTableSchema('test_schema_cache', true) !== null) {
            $command->dropTable('test_schema_cache')->execute();
        }

        $command->createTable('test_schema_cache', ['int1' => 'integer null'])->execute();

        $schemaNotCache = $schema->getTableSchema('test_schema_cache', true);

        self::assertNotNull(
            $schemaNotCache,
            'Fresh table schema must be loadable.',
        );

        $schemaCached = $schema->getTableSchema('test_schema_cache');

        self::assertNotNull(
            $schemaCached,
            'Cached table schema must be loadable.',
        );
        self::assertSame(
            $schemaCached,
            $schemaNotCache,
            'Cache must return the same instance while the table is unchanged.',
        );

        for ($i = 2; $i <= 20; $i++) {
            $command->addColumn('test_schema_cache', 'int' . $i, 'integer null')->execute();

            $schemaCached = $schema->getTableSchema('test_schema_cache');

            self::assertNotNull(
                $schemaCached,
                'Table schema must stay loadable after each column addition.',
            );
            self::assertNotSame(
                $schemaCached,
                $schemaNotCache,
                'Cache must be invalidated after each column addition.',
            );
        }

        self::assertCount(
            20,
            $schemaCached->columns,
            'All added columns must be present.',
        );

        $command->dropTable('test_schema_cache')->execute();
    }

    #[DataProviderExternal(SchemaCacheProvider::class, 'tableSchemaCachePrefixes')]
    #[Depends('testSchemaCache')]
    public function testTableSchemaCacheWithTablePrefixes(
        string $tablePrefix,
        string $tableName,
        string $testTablePrefix,
        string $testTableName,
    ): void {
        $schema = $this->getConnection()->getSchema();

        $schema->db->enableSchemaCache = true;
        $schema->db->tablePrefix = $tablePrefix;
        $schema->db->schemaCache = new ArrayCache();

        $noCacheTable = $schema->getTableSchema($tableName, true);

        self::assertInstanceOf(
            TableSchema::class,
            $noCacheTable,
            'Getting table with cache should return an instance of ' . TableSchema::class . '.',
        );

        $schema->db->tablePrefix = $testTablePrefix;

        $testNoCacheTable = $schema->getTableSchema($testTableName);

        self::assertSame(
            $noCacheTable,
            $testNoCacheTable,
            'Getting table with cache should return the same instance.',
        );

        $schema->db->tablePrefix = $tablePrefix;

        $schema->refreshTableSchema($tableName);

        $refreshedTable = $schema->getTableSchema($tableName, false);

        self::assertInstanceOf(
            TableSchema::class,
            $refreshedTable,
            'Refreshing table should return an instance of ' . TableSchema::class . '.',
        );
        self::assertNotSame(
            $noCacheTable,
            $refreshedTable,
            'Refreshing table should return a different instance.',
        );

        $schema->db->tablePrefix = $testTablePrefix;

        $schema->refreshTableSchema($testTablePrefix);

        $testRefreshedTable = $schema->getTableSchema($testTableName, false);

        self::assertInstanceOf(
            TableSchema::class,
            $testRefreshedTable,
            'Refreshing table should return an instance of ' . TableSchema::class . '.',
        );
        self::assertSame(
            $refreshedTable,
            $testRefreshedTable,
            'Refreshing table should return the same instance.',
        );
        self::assertNotSame(
            $testNoCacheTable,
            $testRefreshedTable,
            'Refreshing table should return a different instance.',
        );
    }
}
