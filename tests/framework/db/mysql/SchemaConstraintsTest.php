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
use yii\db\Constraint;
use yii\db\mysql\QueryBuilder;
use yiiunit\base\db\BaseSchemaConstraints;
use yiiunit\framework\db\mysql\providers\ConstraintsProvider;

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
}
