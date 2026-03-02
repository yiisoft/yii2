<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\framework\db;

use yii\base\InvalidArgumentException;
use yii\db\mssql\QueryBuilder as MssqlQueryBuilder;
use yii\db\pgsql\QueryBuilder as PgsqlQueryBuilder;

class BatchUpdateQueryBuilderWrapperTest extends \yiiunit\TestCase
{
    public function testPgsqlBatchUpdateWithKey(): void
    {
        $queryBuilder = new PgsqlQueryBuilder($this->createConnectionStub('"', '"'));

        $params = [];
        $sql = $queryBuilder->batchUpdate('customer', [
            ['id' => 1, 'status' => 1],
        ], 'id', $params);

        $this->assertSame(
            'UPDATE "customer" SET "status"=CASE WHEN "id"=:qp0 THEN :qp1 ELSE "status" END WHERE "id" IN (:qp0)',
            $sql,
        );
        $this->assertSame([
            ':qp0' => 1,
            ':qp1' => 1,
        ], $params);
    }

    public function testPgsqlBatchUpdateWithTraversableRow(): void
    {
        $queryBuilder = new PgsqlQueryBuilder($this->createConnectionStub('"', '"'));

        $params = [];
        $sql = $queryBuilder->batchUpdate('customer', [
            new \ArrayObject([
                'id' => 1,
                'status' => 1,
            ]),
        ], 'id', $params);

        $this->assertSame(
            'UPDATE "customer" SET "status"=CASE WHEN "id"=:qp0 THEN :qp1 ELSE "status" END WHERE "id" IN (:qp0)',
            $sql,
        );
        $this->assertSame([
            ':qp0' => 1,
            ':qp1' => 1,
        ], $params);
    }

    public function testPgsqlBatchUpdateWithoutKeyDelegatesValidationToParent(): void
    {
        $queryBuilder = new PgsqlQueryBuilder($this->createConnectionStub('"', '"'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Each batch update row must contain the "id" column.');

        $params = [];
        $queryBuilder->batchUpdate('customer', [
            ['status' => 1],
        ], 'id', $params);
    }

    public function testMssqlBatchUpdateWithKey(): void
    {
        $queryBuilder = new MssqlQueryBuilder($this->createConnectionStub('[', ']'));

        $params = [];
        $sql = $queryBuilder->batchUpdate('customer', [
            ['id' => 1, 'status' => 1],
        ], 'id', $params);

        $this->assertSame(
            'UPDATE [customer] SET [status]=CASE WHEN [id]=:qp0 THEN :qp1 ELSE [status] END WHERE [id] IN (:qp0)',
            $sql,
        );
        $this->assertSame([
            ':qp0' => 1,
            ':qp1' => 1,
        ], $params);
    }

    public function testMssqlBatchUpdateWithTraversableRow(): void
    {
        $queryBuilder = new MssqlQueryBuilder($this->createConnectionStub('[', ']'));

        $params = [];
        $sql = $queryBuilder->batchUpdate('customer', [
            new \ArrayObject([
                'id' => 1,
                'status' => 1,
            ]),
        ], 'id', $params);

        $this->assertSame(
            'UPDATE [customer] SET [status]=CASE WHEN [id]=:qp0 THEN :qp1 ELSE [status] END WHERE [id] IN (:qp0)',
            $sql,
        );
        $this->assertSame([
            ':qp0' => 1,
            ':qp1' => 1,
        ], $params);
    }

    public function testMssqlBatchUpdateWithoutKeyDelegatesValidationToParent(): void
    {
        $queryBuilder = new MssqlQueryBuilder($this->createConnectionStub('[', ']'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Each batch update row must contain the "id" column.');

        $params = [];
        $queryBuilder->batchUpdate('customer', [
            ['status' => 1],
        ], 'id', $params);
    }

    private function createConnectionStub(string $quoteBegin, string $quoteEnd)
    {
        $schema = new class {
            public function getTableSchema($table)
            {
                return null;
            }
        };

        return new class ($schema, $quoteBegin, $quoteEnd) {
            private $schema;
            private $quoteBegin;
            private $quoteEnd;

            public function __construct($schema, $quoteBegin, $quoteEnd)
            {
                $this->schema = $schema;
                $this->quoteBegin = $quoteBegin;
                $this->quoteEnd = $quoteEnd;
            }

            public function getSchema()
            {
                return $this->schema;
            }

            public function quoteColumnName($name)
            {
                if (strpos($name, '.') === false) {
                    return $this->quoteBegin . $name . $this->quoteEnd;
                }

                $parts = explode('.', $name);
                foreach ($parts as $i => $part) {
                    $parts[$i] = $this->quoteBegin . $part . $this->quoteEnd;
                }

                return implode('.', $parts);
            }

            public function quoteTableName($name)
            {
                return $this->quoteBegin . $name . $this->quoteEnd;
            }
        };
    }
}
