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
use yii\db\Expression;
use yii\db\sqlite\ColumnSchema;
use yiiunit\base\db\BaseColumnSchema;
use yiiunit\framework\db\sqlite\providers\ColumnSchemaProvider;

/**
 * Unit tests for {@see \yii\db\sqlite\ColumnSchema} column reflection and type-casting for the SQLite driver.
 *
 * {@see ColumnSchemaProvider} for test case data providers.
 */
#[Group('db')]
#[Group('sqlite')]
#[Group('column-schema')]
final class ColumnSchemaTest extends BaseColumnSchema
{
    protected $driverName = 'sqlite';

    /**
     * @param array<string, array<string, mixed>> $columns Expected column metadata.
     */
    #[DataProviderExternal(ColumnSchemaProvider::class, 'columnSchema')]
    public function testColumnSchema(array $columns): void
    {
        parent::testColumnSchema($columns);
    }

    #[DataProviderExternal(ColumnSchemaProvider::class, 'defaultPhpTypecast')]
    public function testDefaultPhpTypecast(
        string $type,
        string $dbType,
        string $phpType,
        mixed $value,
        mixed $expected,
    ): void {
        $column = new ColumnSchema();

        $column->type = $type;
        $column->dbType = $dbType;
        $column->phpType = $phpType;

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
