<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mysql;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use Yii;
use yii\db\Constraint;
use yii\db\ForeignKeyConstraint;
use yii\db\IndexConstraint;
use yii\db\mysql\QueryBuilder;
use yii\db\mysql\Schema;
use yiiunit\base\db\BaseSchemaConstraints;
use yiiunit\framework\db\mysql\providers\ConstraintsProvider;

use function array_filter;
use function array_values;
use function strtoupper;

/**
 * Unit tests for {@see \yii\db\mysql\Schema} constraint and index metadata retrieval for the MySQL driver.
 *
 * {@see ConstraintsProvider} for test case data providers.
 */
#[Group('db')]
#[Group('mysql')]
#[Group('schema')]
final class SchemaConstraintsTest extends BaseSchemaConstraints
{
    public $driverName = 'mysql';

    /**
     * Regression test for https://github.com/yiisoft/yii2/issues/16265.
     */
    public function testIndexMetadataColdLoadExecutesSingleStatisticsQuery(): void
    {
        $db = $this->getConnection(false);

        /** @var Schema $schema */
        $schema = $db->getSchema();

        $schema->refreshTableSchema('T_constraints_2');

        $logger = Yii::getLogger();

        $enableLogging = $db->enableLogging;
        $enableProfiling = $db->enableProfiling;
        $messages = $logger->messages;
        $db->enableLogging = true;
        $db->enableProfiling = false;
        $logger->messages = [];

        $schema->getTablePrimaryKey('T_constraints_2');
        $schema->getTableIndexes('T_constraints_2');
        $schema->getTableUniques('T_constraints_2');

        $queryMessages = array_values(
            array_filter(
                $logger->messages,
                static fn (array $message): bool => $message[2] === 'yii\\db\\Command::query',
            ),
        );

        self::assertCount(
            1,
            $queryMessages,
            'Cold load must execute exactly one query.',
        );

        $metadataSql = strtoupper($queryMessages[0][0]);

        self::assertStringNotContainsString(
            'UNION',
            $metadataSql,
            'Legacy union query must be gone.',
        );
        self::assertStringNotContainsString(
            'TABLE_CONSTRAINTS',
            $metadataSql,
            'Constraint tables must not be scanned.',
        );

        $db->enableLogging = $enableLogging;
        $db->enableProfiling = $enableProfiling;
        $logger->messages = $messages;
    }

    /**
     * @param string[] $expectedColumnNames Expected primary key column names.
     */
    #[DataProviderExternal(ConstraintsProvider::class, 'schemaQualifiedTablePrimaryKey')]
    public function testSchemaQualifiedTablePrimaryKey(string $tableName, array $expectedColumnNames): void
    {
        /** @var Schema $schema */
        $schema = $this->getConnection()->getSchema();

        $primaryKey = $schema->getTablePrimaryKey($tableName, true);

        self::assertInstanceOf(
            Constraint::class,
            $primaryKey,
            'Qualified primary key must be reflected.',
        );
        self::assertNull(
            $primaryKey->name,
            "Primary key name must be 'null'.",
        );
        self::assertSame(
            $expectedColumnNames,
            $primaryKey->columnNames,
            'Columns must come from the qualified table.',
        );
    }

    /**
     * @param string[] $expectedColumnNames Expected index column names.
     */
    #[DataProviderExternal(ConstraintsProvider::class, 'schemaQualifiedTableIndexes')]
    public function testSchemaQualifiedTableIndexes(
        string $tableName,
        string $expectedName,
        array $expectedColumnNames,
    ): void {
        /** @var Schema $schema */
        $schema = $this->getConnection()->getSchema();

        $indexes = $schema->getTableIndexes($tableName, true);

        $index = $this->findConstraintByName($indexes, $expectedName);

        self::assertInstanceOf(
            IndexConstraint::class,
            $index,
            'Qualified index metadata must be reflected.',
        );
        self::assertSame(
            $expectedColumnNames,
            $index->columnNames,
            'Columns must come from the qualified table.',
        );
        self::assertFalse(
            $index->isPrimary,
            'Plain index must not be primary.',
        );
        self::assertFalse(
            $index->isUnique,
            'Plain index must not be unique.',
        );
    }

    /**
     * @param string[] $expectedColumnNames Expected unique constraint column names.
     */
    #[DataProviderExternal(ConstraintsProvider::class, 'schemaQualifiedTableUniques')]
    public function testSchemaQualifiedTableUniques(
        string $tableName,
        string $expectedName,
        array $expectedColumnNames,
    ): void {
        /** @var Schema $schema */
        $schema = $this->getConnection()->getSchema();

        $uniques = $schema->getTableUniques($tableName, true);

        $unique = $this->findConstraintByName($uniques, $expectedName);

        self::assertInstanceOf(
            Constraint::class,
            $unique,
            'Qualified unique constraint must be reflected.',
        );
        self::assertSame(
            $expectedColumnNames,
            $unique->columnNames,
            'Columns must come from the qualified table.',
        );
    }

    /**
     * @param string[] $expectedColumnNames Expected foreign key column names.
     * @param string[] $expectedForeignColumnNames Expected referenced column names.
     */
    #[DataProviderExternal(ConstraintsProvider::class, 'schemaQualifiedTableForeignKeys')]
    public function testSchemaQualifiedTableForeignKeys(
        string $tableName,
        string $expectedName,
        array $expectedColumnNames,
        string|null $expectedForeignSchemaName,
        string $expectedForeignTableName,
        array $expectedForeignColumnNames,
    ): void {
        /** @var Schema $schema */
        $schema = $this->getConnection()->getSchema();

        $foreignKeys = $schema->getTableForeignKeys($tableName, true);

        $foreignKey = $this->findConstraintByName($foreignKeys, $expectedName);

        self::assertInstanceOf(
            ForeignKeyConstraint::class,
            $foreignKey,
            'Qualified foreign key must be reflected.',
        );
        self::assertSame(
            $expectedColumnNames,
            $foreignKey->columnNames,
            'Columns must come from the child table.',
        );
        self::assertSame(
            $expectedForeignSchemaName,
            $foreignKey->foreignSchemaName,
            'Foreign schema must follow the qualified-name semantics.',
        );
        self::assertSame(
            $expectedForeignTableName,
            $foreignKey->foreignTableName,
            'Foreign table must match the referenced table.',
        );
        self::assertSame(
            $expectedForeignColumnNames,
            $foreignKey->foreignColumnNames,
            'Foreign columns must match the referenced key.',
        );
        self::assertSame(
            'CASCADE',
            $foreignKey->onDelete,
            'Delete rule must be reflected.',
        );
        self::assertSame(
            'CASCADE',
            $foreignKey->onUpdate,
            'Update rule must be reflected.',
        );
    }

    public function testSchemaQualifiedTableMetadataIsolation(): void
    {
        /** @var Schema $schema */
        $schema = $this->getConnection()->getSchema();

        $currentPrimaryKey = $schema->getTablePrimaryKey('T_constraints_2', true);
        $crossPrimaryKey = $schema->getTablePrimaryKey('yiitest_cross.T_constraints_2', true);

        self::assertInstanceOf(
            Constraint::class,
            $currentPrimaryKey,
            'Current twin primary key must be reflected.',
        );
        self::assertInstanceOf(
            Constraint::class,
            $crossPrimaryKey,
            'Cross twin primary key must be reflected.',
        );
        self::assertSame(
            ['C_id_1', 'C_id_2'],
            $currentPrimaryKey->columnNames,
            'Current twin must keep its own key.',
        );
        self::assertSame(
            ['C_cross_id'],
            $crossPrimaryKey->columnNames,
            'Cross twin must keep its own key.',
        );

        $currentUniques = $schema->getTableUniques('T_constraints_2');
        $crossUniques = $schema->getTableUniques('yiitest_cross.T_constraints_2');

        self::assertCount(
            1,
            $currentUniques,
            'Current twin must expose one unique constraint.',
        );
        self::assertSame(
            'CN_constraints_2_multi',
            $currentUniques[0]->name,
            'Unique name must be database-local.',
        );
        self::assertCount(
            1,
            $crossUniques,
            'Cross twin must expose one unique constraint.',
        );
        self::assertSame(
            'CN_cross_unique',
            $crossUniques[0]->name,
            'Unique name must be database-local.',
        );
        self::assertNull(
            $this->findConstraintByName($schema->getTableIndexes('T_constraints_2'), 'CN_cross_single'),
            'Cross twin metadata must not bleed into the current twin.',
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
        /** @var QueryBuilder $qb */
        $qb = $this->getConnection(false)->getQueryBuilder();

        parent::testTableSchemaConstraints(
            $tableName,
            $type,
            ConstraintsProvider::prepareConstraintsExpected(
                $qb->isMariaDb(),
                $tableName,
                $type,
                $expected,
            ),
        );
    }

    /**
     * @param Constraint|bool|array<array-key, mixed>|null $expected Expected constraint metadata.
     */
    #[DataProviderExternal(ConstraintsProvider::class, 'constraints')]
    public function testTableSchemaConstraintsWithPdoLowercase(string $tableName, string $type, mixed $expected): void
    {
        /** @var QueryBuilder $qb */
        $qb = $this->getConnection(false)->getQueryBuilder();

        parent::testTableSchemaConstraintsWithPdoLowercase(
            $tableName,
            $type,
            ConstraintsProvider::prepareConstraintsExpected(
                $qb->isMariaDb(),
                $tableName,
                $type,
                $expected,
            ),
        );
    }

    /**
     * @param Constraint|bool|array<array-key, mixed>|null $expected Expected constraint metadata.
     */
    #[DataProviderExternal(ConstraintsProvider::class, 'constraints')]
    public function testTableSchemaConstraintsWithPdoUppercase(string $tableName, string $type, mixed $expected): void
    {
        /** @var QueryBuilder $qb */
        $qb = $this->getConnection(false)->getQueryBuilder();

        parent::testTableSchemaConstraintsWithPdoUppercase(
            $tableName,
            $type,
            ConstraintsProvider::prepareConstraintsExpected(
                $qb->isMariaDb(),
                $tableName,
                $type,
                $expected,
            ),
        );
    }

    /**
     * @param Constraint[] $constraints Constraint metadata.
     */
    private function findConstraintByName(array $constraints, string $name): Constraint|null
    {
        foreach ($constraints as $constraint) {
            if ($constraint->name === $name) {
                return $constraint;
            }
        }

        return null;
    }
}
