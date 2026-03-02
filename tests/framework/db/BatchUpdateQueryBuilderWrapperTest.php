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
use yii\db\oci\QueryBuilder as OciQueryBuilder;
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

    public function testOciBatchUpdateWithKey(): void
    {
        $queryBuilder = new OciQueryBuilder($this->createConnectionStub('"', '"'));

        $params = [];
        $sql = $queryBuilder->batchUpdate('customer', [
            ['id' => 1, 'status' => 1],
        ], 'id', $params);

        $this->assertSame(
            'MERGE INTO "customer" T USING (SELECT :qp0 AS "_bk", :qp1 AS "_v0", 1 AS "_s0" FROM DUAL) S ON (T."id"=S."_bk" OR (T."id" IS NULL AND S."_bk" IS NULL)) WHEN MATCHED THEN UPDATE SET T."status"=CASE WHEN S."_s0"=1 THEN S."_v0" ELSE T."status" END',
            $sql,
        );
        $this->assertSame([
            ':qp0' => 1,
            ':qp1' => 1,
        ], $params);
    }

    public function testOciBatchUpdateWithTraversableRow(): void
    {
        $queryBuilder = new OciQueryBuilder($this->createConnectionStub('"', '"'));

        $params = [];
        $sql = $queryBuilder->batchUpdate('customer', [
            new \ArrayObject([
                'id' => 1,
                'status' => 1,
            ]),
        ], 'id', $params);

        $this->assertSame(
            'MERGE INTO "customer" T USING (SELECT :qp0 AS "_bk", :qp1 AS "_v0", 1 AS "_s0" FROM DUAL) S ON (T."id"=S."_bk" OR (T."id" IS NULL AND S."_bk" IS NULL)) WHEN MATCHED THEN UPDATE SET T."status"=CASE WHEN S."_s0"=1 THEN S."_v0" ELSE T."status" END',
            $sql,
        );
        $this->assertSame([
            ':qp0' => 1,
            ':qp1' => 1,
        ], $params);
    }

    public function testOciBatchUpdateWithTableSchemaAddsCast(): void
    {
        $schema = (object) [
            'columns' => [
                'int_col' => new class {
                    public $dbType = 'NUMBER(10)';

                    public function dbTypecast($value)
                    {
                        return $value === null ? null : (int) $value;
                    }
                },
                'float_col' => new class {
                    public $dbType = 'NUMBER';

                    public function dbTypecast($value)
                    {
                        return $value === null ? null : (float) $value;
                    }
                },
            ],
        ];
        $queryBuilder = new OciQueryBuilder($this->createConnectionStub('"', '"', [
            'type' => $schema,
        ]));

        $params = [];
        $sql = $queryBuilder->batchUpdate('type', [
            ['int_col' => '1', 'float_col' => '2.5'],
        ], 'int_col', $params);

        $this->assertStringStartsWith('MERGE INTO "type" T USING (SELECT CAST(:qp0 AS NUMBER(10)) AS "_bk", CAST(:qp1 AS NUMBER) AS "_v0", 1 AS "_s0" FROM DUAL) S ON (T."int_col"=S."_bk" OR (T."int_col" IS NULL AND S."_bk" IS NULL)) WHEN MATCHED THEN UPDATE SET T."float_col"=CASE WHEN S."_s0"=1 THEN S."_v0" ELSE T."float_col" END', $sql);
        $this->assertSame([
            ':qp0' => 1,
            ':qp1' => 2.5,
        ], $params);
    }

    public function testOciBatchUpdateWithoutKeyDelegatesValidationToParent(): void
    {
        $queryBuilder = new OciQueryBuilder($this->createConnectionStub('"', '"'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Each batch update row must contain the "id" column.');

        $params = [];
        $queryBuilder->batchUpdate('customer', [
            ['status' => 1],
        ], 'id', $params);
    }

    public function testOciBatchUpdateInvalidRowType(): void
    {
        $queryBuilder = new OciQueryBuilder($this->createConnectionStub('"', '"'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Each batch update row must be an array.');

        $params = [];
        $queryBuilder->batchUpdate('customer', [
            'invalid',
        ], 'id', $params);
    }

    public function testOciBatchUpdateInvalidKeyValue(): void
    {
        $queryBuilder = new OciQueryBuilder($this->createConnectionStub('"', '"'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Batch update key values must be scalar or null. Column "id" contains an invalid value.');

        $params = [];
        $queryBuilder->batchUpdate('customer', [
            [
                'id' => new \yii\db\Expression('1'),
                'status' => 1,
            ],
        ], 'id', $params);
    }

    public function testOciBatchUpdateDuplicateKeyValues(): void
    {
        $queryBuilder = new OciQueryBuilder($this->createConnectionStub('"', '"'));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Duplicate batch update key value found for "id".');

        $params = [];
        $queryBuilder->batchUpdate('customer', [
            ['id' => 1, 'status' => 1],
            ['id' => 1, 'status' => 0],
        ], 'id', $params);
    }

    public function testOciBatchUpdateWithoutUpdatableColumns(): void
    {
        $queryBuilder = new OciQueryBuilder($this->createConnectionStub('"', '"'));

        $params = [];
        $sql = $queryBuilder->batchUpdate('customer', [
            ['id' => 1],
        ], 'id', $params);

        $this->assertSame('', $sql);
        $this->assertSame([], $params);
    }

    public function testOciBatchUpdateWithSparseRowsBuildsNullSourceValue(): void
    {
        $queryBuilder = new OciQueryBuilder($this->createConnectionStub('"', '"'));

        $params = [];
        $sql = $queryBuilder->batchUpdate('customer', [
            ['id' => 1, 'status' => 1, 'name' => 'Tom'],
            ['id' => 2, 'status' => 0],
        ], 'id', $params);

        $this->assertStringContainsString('NULL AS "_v1", 0 AS "_s1"', $sql);
        $this->assertSame([
            ':qp0' => 1,
            ':qp1' => 1,
            ':qp2' => 'Tom',
            ':qp3' => 2,
            ':qp4' => 0,
        ], $params);
    }

    public function testOciBatchUpdateWithSchemaCastsAndLobBranches(): void
    {
        $schema = (object) [
            'columns' => [
                'int_col' => new class {
                    public $dbType = 'NUMBER(10)';

                    public function dbTypecast($value)
                    {
                        return $value === null ? null : (int) $value;
                    }
                },
                'num_col' => new class {
                    public $dbType = 'NUMBER';

                    public function dbTypecast($value)
                    {
                        return $value === null ? null : (float) $value;
                    }
                },
                'clob_col' => new class {
                    public $dbType = 'CLOB';

                    public function dbTypecast($value)
                    {
                        return $value;
                    }
                },
                'flag_col' => new class {
                    public $dbType = 'NUMBER(1)';

                    public function dbTypecast($value)
                    {
                        return $value === null ? null : (int) $value;
                    }
                },
            ],
        ];
        $queryBuilder = new OciQueryBuilder($this->createConnectionStub('"', '"', [
            'type' => $schema,
        ]));

        $params = [];
        $sql = $queryBuilder->batchUpdate('type', [
            ['int_col' => 1, 'num_col' => 2.5, 'clob_col' => 'abc'],
            ['int_col' => 2, 'flag_col' => 1],
        ], 'int_col', $params);

        $this->assertStringContainsString('CAST(:qp1 AS NUMBER) AS "_v0"', $sql);
        $this->assertStringContainsString(':qp2 AS "_v1"', $sql);
        $this->assertStringContainsString('CAST(NULL AS NUMBER) AS "_v0"', $sql);
        $this->assertStringContainsString('NULL AS "_v1"', $sql);
        $this->assertStringContainsString('CAST(:qp4 AS NUMBER(1)) AS "_v2"', $sql);
        $this->assertSame([
            ':qp0' => 1,
            ':qp1' => 2.5,
            ':qp2' => 'abc',
            ':qp3' => 2,
            ':qp4' => 1,
        ], $params);
    }

    public function testOciBatchUpdateWithExpressionValue(): void
    {
        $queryBuilder = new OciQueryBuilder($this->createConnectionStub('"', '"'));

        $params = [];
        $sql = $queryBuilder->batchUpdate('customer', [
            [
                'id' => 1,
                'status' => new \yii\db\Expression('UPPER(status)'),
            ],
        ], 'id', $params);

        $this->assertStringContainsString('UPPER(status) AS "_v0"', $sql);
        $this->assertSame([
            ':qp0' => 1,
        ], $params);
    }

    private function createConnectionStub(string $quoteBegin, string $quoteEnd, array $tableSchemas = [])
    {
        $schema = new class ($tableSchemas) {
            private $tableSchemas;

            public function __construct(array $tableSchemas)
            {
                $this->tableSchemas = $tableSchemas;
            }

            public function getTableSchema($table)
            {
                return isset($this->tableSchemas[$table]) ? $this->tableSchemas[$table] : null;
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
