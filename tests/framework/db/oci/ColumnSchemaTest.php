<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\oci;

use PDO;
use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use yii\db\Expression;
use yii\db\PdoValue;
use yii\db\oci\ColumnSchema;
use yii\db\oci\Schema;
use yiiunit\framework\db\DatabaseTestCase;
use yiiunit\framework\db\oci\providers\ColumnSchemaProvider;

/**
 * Unit tests for {@see \yii\db\oci\ColumnSchema} BLOB binding and default value type-casting for the Oracle driver.
 *
 * {@see ColumnSchemaProvider} for test case data providers.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('db')]
#[Group('oci')]
#[Group('column-schema')]
final class ColumnSchemaTest extends DatabaseTestCase
{
    protected $driverName = 'oci';

    #[DataProviderExternal(ColumnSchemaProvider::class, 'dbTypecast')]
    public function testDbTypecast(string $type, string $dbType, mixed $value, mixed $expected): void
    {
        $column = new ColumnSchema();

        $column->type = $type;
        $column->dbType = $dbType;

        $result = $column->dbTypecast($value);

        if ($expected !== Expression::class) {
            self::assertSame(
                $expected,
                $result,
                'Converted value must match.',
            );

            return;
        }

        self::assertInstanceOf(
            Expression::class,
            $result,
            'BLOB string must yield an Expression.',
        );
        self::assertStringContainsString(
            'TO_BLOB(UTL_RAW.CAST_TO_RAW(',
            $result->expression,
            "Expression must wrap the value in 'TO_BLOB'.",
        );
    }

    public function testDbTypecastBlobPdoValueLob(): void
    {
        $column = new ColumnSchema();

        $column->type = Schema::TYPE_BINARY;
        $column->dbType = 'BLOB';

        $result = $column->dbTypecast(new PdoValue('binary data', PDO::PARAM_LOB));

        self::assertInstanceOf(
            Expression::class,
            $result,
            "LOB 'PdoValue' must yield an Expression.",
        );
        self::assertStringContainsString(
            'TO_BLOB(UTL_RAW.CAST_TO_RAW(',
            $result->expression,
            "Expression must wrap the value in 'TO_BLOB'.",
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
