<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db;

use yii\base\InvalidArgumentException;
use yii\db\ColumnSchema;
use yii\db\TableSchema;
use yiiunit\TestCase;

/**
 * @group db
 */
class TableSchemaTest extends TestCase
{
    private function createTableSchema(array $columnNames = ['id', 'name', 'email']): TableSchema
    {
        $table = new TableSchema();
        $table->name = 'test_table';
        $table->fullName = 'test_table';

        foreach ($columnNames as $name) {
            $column = new ColumnSchema();
            $column->name = $name;
            $column->isPrimaryKey = false;
            $table->columns[$name] = $column;
        }

        return $table;
    }

    public function testGetColumnReturnsColumnSchema(): void
    {
        $table = $this->createTableSchema();

        $column = $table->getColumn('name');

        self::assertInstanceOf(ColumnSchema::class, $column);
        self::assertSame('name', $column->name);
    }

    public function testGetColumnReturnsNullForNonExistent(): void
    {
        $table = $this->createTableSchema();

        self::assertNull($table->getColumn('nonexistent'));
    }

    public function testGetColumnNames(): void
    {
        $table = $this->createTableSchema(['id', 'name', 'email']);

        self::assertSame(['id', 'name', 'email'], $table->getColumnNames());
    }

    public function testGetColumnNamesEmpty(): void
    {
        $table = $this->createTableSchema([]);

        self::assertSame([], $table->getColumnNames());
    }

    public function testColumnNamesProperty(): void
    {
        $table = $this->createTableSchema(['a', 'b']);

        self::assertSame(['a', 'b'], $table->columnNames);
    }

    public function testFixPrimaryKeyWithSingleKey(): void
    {
        $table = $this->createTableSchema();

        $table->fixPrimaryKey('id');

        self::assertSame(['id'], $table->primaryKey);
        self::assertTrue($table->columns['id']->isPrimaryKey);
        self::assertFalse($table->columns['name']->isPrimaryKey);
        self::assertFalse($table->columns['email']->isPrimaryKey);
    }

    public function testFixPrimaryKeyWithCompositeKey(): void
    {
        $table = $this->createTableSchema(['user_id', 'role_id', 'data']);

        $table->fixPrimaryKey(['user_id', 'role_id']);

        self::assertSame(['user_id', 'role_id'], $table->primaryKey);
        self::assertTrue($table->columns['user_id']->isPrimaryKey);
        self::assertTrue($table->columns['role_id']->isPrimaryKey);
        self::assertFalse($table->columns['data']->isPrimaryKey);
    }

    public function testFixPrimaryKeyResetsOldPrimaryKey(): void
    {
        $table = $this->createTableSchema();
        $table->columns['id']->isPrimaryKey = true;
        $table->primaryKey = ['id'];

        $table->fixPrimaryKey('name');

        self::assertSame(['name'], $table->primaryKey);
        self::assertFalse($table->columns['id']->isPrimaryKey);
        self::assertTrue($table->columns['name']->isPrimaryKey);
    }

    public function testFixPrimaryKeyThrowsOnNonExistentColumn(): void
    {
        $table = $this->createTableSchema();

        $this->expectException(InvalidArgumentException::class);
        $table->fixPrimaryKey('nonexistent');
    }

    public function testFixPrimaryKeyThrowsOnPartialComposite(): void
    {
        $table = $this->createTableSchema();

        $this->expectException(InvalidArgumentException::class);
        $table->fixPrimaryKey(['id', 'missing']);
    }

    public function testFixPrimaryKeyWithEmptyArray(): void
    {
        $table = $this->createTableSchema();
        $table->columns['id']->isPrimaryKey = true;
        $table->primaryKey = ['id'];

        $table->fixPrimaryKey([]);

        self::assertSame([], $table->primaryKey);
        self::assertFalse($table->columns['id']->isPrimaryKey);
    }

    public function testDefaultPropertyValues(): void
    {
        $table = new TableSchema();

        self::assertSame([], $table->primaryKey);
        self::assertNull($table->sequenceName);
        self::assertSame([], $table->foreignKeys);
        self::assertSame([], $table->columns);
    }

    public function testSchemaNameProperty(): void
    {
        $table = new TableSchema();
        $table->schemaName = 'public';
        $table->name = 'users';
        $table->fullName = 'public.users';

        self::assertSame('public', $table->schemaName);
        self::assertSame('users', $table->name);
        self::assertSame('public.users', $table->fullName);
    }

    public function testForeignKeysStructure(): void
    {
        $table = new TableSchema();
        $table->foreignKeys = [
            ['users', 'user_id' => 'id'],
            ['roles', 'role_id' => 'id', 'tenant_id' => 'tenant_id'],
        ];

        self::assertCount(2, $table->foreignKeys);
        self::assertSame('users', $table->foreignKeys[0][0]);
        self::assertSame('id', $table->foreignKeys[0]['user_id']);
        self::assertSame('roles', $table->foreignKeys[1][0]);
        self::assertSame('id', $table->foreignKeys[1]['role_id']);
        self::assertSame('tenant_id', $table->foreignKeys[1]['tenant_id']);
    }
}
