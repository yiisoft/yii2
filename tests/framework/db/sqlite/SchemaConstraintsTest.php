<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\sqlite;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use yii\db\Constraint;
use yiiunit\base\db\BaseSchemaConstraints;
use yiiunit\framework\db\sqlite\providers\ConstraintsProvider;

/**
 * Unit tests for {@see \yii\db\sqlite\Schema} constraint and index metadata retrieval for the SQLite driver.
 *
 * {@see ConstraintsProvider} for test case data providers.
 */
#[Group('db')]
#[Group('sqlite')]
#[Group('schema')]
final class SchemaConstraintsTest extends BaseSchemaConstraints
{
    protected $driverName = 'sqlite';

    public function testCompositeFk(): void
    {
        $schema = $this->getConnection()->getSchema();

        $table = $schema->getTableSchema('composite_fk');

        self::assertCount(
            1,
            $table->foreignKeys,
            'There must be exactly one foreign key defined for the table.',
        );
        self::assertTrue(
            isset($table->foreignKeys[0]),
            'Foreign key must be defined.',
        );
        self::assertSame(
            'order_item',
            $table->foreignKeys[0][0],
            'Referenced table name does not match.',
        );
        self::assertSame(
            'order_id',
            $table->foreignKeys[0]['order_id'],
            'Referenced column name does not match.',
        );
        self::assertSame(
            'item_id',
            $table->foreignKeys[0]['item_id'],
            'Referenced column name does not match.',
        );
    }

    /**
     * @param Constraint|bool|array<array-key, mixed>|null $expected Expected constraint metadata.
     */
    #[DataProviderExternal(ConstraintsProvider::class, 'constraints')]
    public function testTableSchemaConstraints(
        string $tableName,
        string $type,
        Constraint|bool|array|null $expected,
    ): void {
        parent::testTableSchemaConstraints($tableName, $type, $expected);
    }

    /**
     * @param Constraint|bool|array<array-key, mixed>|null $expected Expected constraint metadata.
     */
    #[DataProviderExternal(ConstraintsProvider::class, 'constraints')]
    public function testTableSchemaConstraintsWithPdoLowercase(string $tableName, string $type, mixed $expected): void
    {
        parent::testTableSchemaConstraintsWithPdoLowercase($tableName, $type, $expected);
    }

    /**
     * @param Constraint|bool|array<array-key, mixed>|null $expected Expected constraint metadata.
     */
    #[DataProviderExternal(ConstraintsProvider::class, 'constraints')]
    public function testTableSchemaConstraintsWithPdoUppercase(string $tableName, string $type, mixed $expected): void
    {
        parent::testTableSchemaConstraintsWithPdoUppercase($tableName, $type, $expected);
    }
}
