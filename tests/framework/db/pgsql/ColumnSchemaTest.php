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
use yii\db\ArrayExpression;
use yii\db\Expression;
use yii\db\JsonExpression;
use yii\db\pgsql\ColumnSchema;
use yiiunit\base\db\BaseColumnSchema;
use yiiunit\framework\db\pgsql\providers\ColumnSchemaProvider;

/**
 * Unit tests for {@see \yii\db\pgsql\ColumnSchema} type-casting and default value conversion for the PostgreSQL driver.
 *
 * {@see ColumnSchemaProvider} for test case data providers.
 */
#[Group('db')]
#[Group('pgsql')]
#[Group('column-schema')]
final class ColumnSchemaTest extends BaseColumnSchema
{
    protected $driverName = 'pgsql';

    /**
     * @param array<string, array<string, mixed>> $columns Expected column metadata.
     */
    #[DataProviderExternal(ColumnSchemaProvider::class, 'columnSchema')]
    public function testColumnSchema(array $columns): void
    {
        parent::testColumnSchema($columns);
    }

    public function testDbTypecastArrayWithDimensionReturnsArrayExpression(): void
    {
        $column = $this->createColumn('integer', 'int4', 'integer', 1);

        $result = $column->dbTypecast([1, 2, 3]);

        self::assertInstanceOf(
            ArrayExpression::class,
            $result,
            'Array column must yield an ArrayExpression.',
        );
        self::assertSame(
            [1, 2, 3],
            $result->getValue(),
            'Array value must match.',
        );
        self::assertSame(
            'int4',
            $result->getType(),
            "Array type must match the column 'dbType'.",
        );
        self::assertSame(
            1,
            $result->getDimension(),
            'Array dimension must match.',
        );
    }

    public function testDbTypecastExpressionPassesThrough(): void
    {
        $column = $this->createColumn('string', 'varchar', 'string');

        $expression = new Expression('NOW()');

        self::assertSame(
            $expression,
            $column->dbTypecast($expression),
            'Expression must pass through unchanged.',
        );
    }

    public function testDbTypecastJsonbReturnsJsonExpression(): void
    {
        $column = $this->createColumn('json', 'jsonb', 'string');

        $result = $column->dbTypecast(['key' => 'value']);

        self::assertInstanceOf(
            JsonExpression::class,
            $result,
            'JSONB column must yield a JsonExpression.',
        );
        self::assertSame(
            ['key' => 'value'],
            $result->getValue(),
            'JSONB value must match.',
        );
        self::assertSame(
            'jsonb',
            $result->getType(),
            "JSONB type must match the column 'dbType'.",
        );
    }

    public function testDbTypecastJsonReturnsJsonExpression(): void
    {
        $column = $this->createColumn('json', 'json', 'string');

        $result = $column->dbTypecast(['key' => 'value']);

        self::assertInstanceOf(
            JsonExpression::class,
            $result,
            'JSON column must yield a JsonExpression.',
        );
        self::assertSame(
            ['key' => 'value'],
            $result->getValue(),
            'JSON value must match.',
        );
        self::assertSame(
            'json',
            $result->getType(),
            "JSON type must match the column 'dbType'.",
        );
    }

    public function testDbTypecastNullReturnsNull(): void
    {
        $column = $this->createColumn('string', 'varchar', 'string');

        self::assertNull(
            $column->dbTypecast(null),
            "Result must be 'null'.",
        );
    }

    public function testDbTypecastRegularValueFallsThrough(): void
    {
        $column = $this->createColumn('integer', 'int4', 'integer');

        self::assertSame(
            42,
            $column->dbTypecast('42'),
            "Scalar value must be cast to 'integer'.",
        );
    }

    #[DataProviderExternal(ColumnSchemaProvider::class, 'defaultPhpTypecast')]
    public function testDefaultPhpTypecast(
        string $type,
        string $dbType,
        string $phpType,
        mixed $value,
        mixed $expected,
    ): void {
        $column = $this->createColumn($type, $dbType, $phpType);

        $result = $column->defaultPhpTypecast($value);

        if (!$expected instanceof Expression) {
            self::assertSame(
                $expected,
                $result,
                'Converted default must match.',
            );

            return;
        }

        self::assertInstanceOf(
            Expression::class,
            $result,
            'Default must yield an Expression.',
        );
        self::assertSame(
            $expected->expression,
            $result->expression,
            'Expression SQL must match.',
        );
    }

    #[DataProviderExternal(ColumnSchemaProvider::class, 'phpTypecast')]
    public function testPhpTypecast(
        string $type,
        string $dbType,
        string $phpType,
        mixed $value,
        mixed $expected,
    ): void {
        $column = $this->createColumn($type, $dbType, $phpType);

        self::assertSame(
            $expected,
            $column->phpTypecast($value),
            'Converted value must match.',
        );
    }

    public function testPhpTypecastArrayInputWalksValues(): void
    {
        $column = $this->createColumn('integer', 'int4', 'integer', 1);

        self::assertSame(
            [1, 2, 3],
            $column->phpTypecast(['1', '2', '3']),
            "Array values must be cast to 'integer'.",
        );
    }

    public function testPhpTypecastArrayNullReturnsNull(): void
    {
        $column = $this->createColumn('integer', 'int4', 'integer', 1);

        self::assertNull(
            $column->phpTypecast(null),
            "Array column must return 'null'.",
        );
    }

    public function testPhpTypecastArrayStringParsesToArray(): void
    {
        $column = $this->createColumn('integer', 'int4', 'integer', 1);

        self::assertSame(
            [1, 2, 3],
            $column->phpTypecast('{1,2,3}'),
            "Array literal must parse to a typed 'array'.",
        );
    }

    public function testPhpTypecastNestedArrayStringParsesRecursively(): void
    {
        $column = $this->createColumn('integer', 'int4', 'integer', 2);

        self::assertSame(
            [[1, 2], [3, 4]],
            $column->phpTypecast('{{1,2},{3,4}}'),
            'Nested literal must parse recursively.',
        );
    }

    private function createColumn(string $type, string $dbType, string $phpType, int $dimension = 0): ColumnSchema
    {
        $column = new ColumnSchema();

        $column->type = $type;
        $column->dbType = $dbType;
        $column->phpType = $phpType;
        $column->dimension = $dimension;

        return $column;
    }
}
