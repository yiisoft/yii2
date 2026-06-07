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
use yii\db\Constraint;
use yiiunit\base\db\BaseSchemaConstraints;
use yiiunit\framework\db\mssql\providers\ConstraintsProvider;

use function array_values;

/**
 * Unit tests for {@see \yii\db\mssql\Schema} constraint and index metadata retrieval for the MSSQL driver.
 *
 * {@see ConstraintsProvider} for test case data providers.
 */
#[Group('db')]
#[Group('mssql')]
#[Group('schema')]
final class SchemaConstraintsTest extends BaseSchemaConstraints
{
    public $driverName = 'sqlsrv';

    public function testFindUniqueIndexes(): void
    {
        $db = $this->getConnection();

        $table = $db->getSchema()->getTableSchema('T_upsert');
        $indexes = $db->getSchema()->findUniqueIndexes($table);

        self::assertCount(
            2,
            $indexes,
            "Should return the unique constraints defined on 'T_upsert'.",
        );
        self::assertContains(
            ['email'],
            array_values($indexes),
            "Single-column unique constraint on 'email' is missing.",
        );
        self::assertContains(
            ['email', 'recovery_email'],
            array_values($indexes),
            "Composite unique constraint on 'email, recovery_email' is missing.",
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
