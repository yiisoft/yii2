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
use yii\db\Expression;
use yii\db\mysql\ColumnSchema;
use yii\db\mysql\QueryBuilder;
use yiiunit\base\db\BaseColumnSchema;
use yiiunit\framework\db\mysql\providers\ColumnSchemaProvider;

/**
 * Unit tests for {@see \yii\db\mysql\ColumnSchema} column reflection and type-casting for the MySQL driver.
 *
 * {@see ColumnSchemaProvider} for test case data providers.
 */
#[Group('db')]
#[Group('mysql')]
#[Group('column-schema')]
final class ColumnSchemaTest extends BaseColumnSchema
{
    protected $driverName = 'mysql';

    /**
     * @param array<string, array<string, mixed>> $columns Expected column metadata.
     */
    #[DataProviderExternal(ColumnSchemaProvider::class, 'columnSchema')]
    public function testColumnSchema(array $columns): void
    {
        /** @var QueryBuilder $qb */
        $qb = $this->getConnection(false)->getQueryBuilder();

        parent::testColumnSchema(ColumnSchemaProvider::prepareColumnSchema($qb->isMariaDb(), $columns));
    }

    #[DataProviderExternal(ColumnSchemaProvider::class, 'defaultPhpTypecast')]
    public function testDefaultPhpTypecast(
        string $type,
        string $dbType,
        string $phpType,
        mixed $value,
        mixed $expected,
        bool $isDefaultExpression = false,
    ): void {
        $column = new ColumnSchema();

        $column->type = $type;
        $column->dbType = $dbType;
        $column->phpType = $phpType;
        $column->isDefaultExpression = $isDefaultExpression;

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
}
