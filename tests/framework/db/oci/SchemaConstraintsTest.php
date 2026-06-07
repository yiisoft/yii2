<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\oci;

use Exception;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use yii\db\Constraint;
use yiiunit\base\db\BaseSchemaConstraints;
use yiiunit\framework\db\oci\providers\ConstraintsProvider;

/**
 * Unit tests for {@see \yii\db\oci\Schema} constraint and index metadata retrieval for the Oracle driver.
 *
 * {@see ConstraintsProvider} for test case data providers.
 */
#[Group('db')]
#[Group('oci')]
#[Group('schema')]
final class SchemaConstraintsTest extends BaseSchemaConstraints
{
    public $driverName = 'oci';

    public function testCompositeFk(): void
    {
        $schema = $this->getConnection()->getSchema();

        $table = $schema->getTableSchema('composite_fk');

        $foreignKey = 'tableName';

        self::assertCount(
            1,
            $table->foreignKeys,
            'Number of foreign keys does not match the expected count.',
        );
        self::assertTrue(
            isset($table->foreignKeys[$foreignKey]),
            "Foreign key '{$foreignKey}' is missing in the table schema.",
        );
        self::assertSame(
            'order_item',
            $table->foreignKeys[$foreignKey][0],
            "Referenced table name for foreign key '{$foreignKey}' does not match the expected value.",
        );
        self::assertSame(
            'order_id',
            $table->foreignKeys[$foreignKey]['order_id'],
            "Referenced column name for foreign key '{$foreignKey}' does not match the expected value.",
        );
        self::assertSame(
            'item_id',
            $table->foreignKeys[$foreignKey]['item_id'],
            "Referenced column name for foreign key '{$foreignKey}' does not match the expected value.",
        );
    }

    public function testFindUniqueIndexes(): void
    {
        $db = $this->getConnection();

        try {
            $db->createCommand()->dropTable('uniqueIndex')->execute();
        } catch (Exception) {
        }
        $db->createCommand()->createTable(
            'uniqueIndex',
            [
                'somecol' => 'string',
                'someCol2' => 'string',
                'someCol3' => 'string',
            ],
        )->execute();

        $schema = $db->getSchema();
        $uniqueIndexes = $schema->findUniqueIndexes($schema->getTableSchema('uniqueIndex', true));

        self::assertSame(
            [],
            $uniqueIndexes,
            'There should be no unique indexes in the table.',
        );

        $db->createCommand()->createIndex('somecolUnique', 'uniqueIndex', 'somecol', true)->execute();
        $uniqueIndexes = $schema->findUniqueIndexes($schema->getTableSchema('uniqueIndex', true));

        self::assertEquals(
            ['somecolUnique' => ['somecol']],
            $uniqueIndexes,
            'Unique indexes do not match after creating unique index on "somecol".',
        );

        // create another column with upper case letter that fails postgres
        // see https://github.com/yiisoft/yii2/issues/10613
        $db->createCommand()->createIndex('someCol2Unique', 'uniqueIndex', 'someCol2', true)->execute();
        $uniqueIndexes = $schema->findUniqueIndexes($schema->getTableSchema('uniqueIndex', true));

        self::assertEquals(
            [
                'somecolUnique' => ['somecol'],
                'someCol2Unique' => ['someCol2'],
            ],
            $uniqueIndexes,
            "Unique indexes do not match after creating unique index on 'someCol2'.",
        );

        // see https://github.com/yiisoft/yii2/issues/13814
        $db->createCommand()->createIndex('another unique index', 'uniqueIndex', 'someCol3', true)->execute();
        $uniqueIndexes = $schema->findUniqueIndexes($schema->getTableSchema('uniqueIndex', true));

        self::assertEquals(
            [
                'somecolUnique' => ['somecol'],
                'someCol2Unique' => ['someCol2'],
                'another unique index' => ['someCol3'],
            ],
            $uniqueIndexes,
            "Unique indexes do not match after creating unique index on 'someCol3'.",
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
