<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\pgsql;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use yii\db\Constraint;
use yiiunit\base\db\BaseSchemaConstraints;
use yiiunit\framework\db\pgsql\providers\ConstraintsProvider;

/**
 * Unit tests for {@see \yii\db\pgsql\Schema} constraint and index metadata retrieval for the PostgreSQL driver.
 *
 * {@see ConstraintsProvider} for test case data providers.
 */
#[Group('db')]
#[Group('pgsql')]
#[Group('schema')]
final class SchemaConstraintsTest extends BaseSchemaConstraints
{
    public $driverName = 'pgsql';

    public function testCompositeFk(): void
    {
        $schema = $this->getConnection()->schema;

        $table = $schema->getTableSchema('composite_fk');

        self::assertCount(
            1,
            $table->foreignKeys,
            'Number of foreign keys does not match the expected count.',
        );
        self::assertTrue(
            isset($table->foreignKeys['fk_composite_fk_order_item']),
            "Foreign key 'fk_composite_fk_order_item' is missing in the table schema.",
        );
        self::assertSame(
            'order_item',
            $table->foreignKeys['fk_composite_fk_order_item'][0],
            "Referenced table name for foreign key 'fk_composite_fk_order_item' does not match the expected value.",
        );
        self::assertSame(
            'order_id',
            $table->foreignKeys['fk_composite_fk_order_item']['order_id'],
            "Referenced column name for foreign key 'fk_composite_fk_order_item' does not match the expected value.",
        );
        self::assertSame(
            'item_id',
            $table->foreignKeys['fk_composite_fk_order_item']['item_id'],
            "Referenced column name for foreign key 'fk_composite_fk_order_item' does not match the expected value.",
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
