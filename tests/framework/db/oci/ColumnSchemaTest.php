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
use yii\db\oci\LobValue;
use yii\db\oci\Schema;
use yiiunit\base\db\BaseColumnSchema;
use yiiunit\framework\db\oci\providers\ColumnSchemaProvider;
use function fopen;

/**
 * Unit tests for {@see \yii\db\oci\ColumnSchema} BLOB binding and default value type-casting for the Oracle driver.
 *
 * {@see ColumnSchemaProvider} for test case data providers.
 */
#[Group('db')]
#[Group('oci')]
#[Group('column-schema')]
final class ColumnSchemaTest extends BaseColumnSchema
{
    protected $driverName = 'oci';

    /**
     * @param array<string, array<string, mixed>> $columns Expected column metadata.
     */
    #[DataProviderExternal(ColumnSchemaProvider::class, 'columnSchema')]
    public function testColumnSchema(array $columns): void
    {
        parent::testColumnSchema($columns);
    }

    #[DataProviderExternal(ColumnSchemaProvider::class, 'dbTypecast')]
    public function testDbTypecast(string $type, string $dbType, mixed $value, mixed $expected): void
    {
        $column = new ColumnSchema();

        $column->name = 'blob_col';
        $column->type = $type;
        $column->dbType = $dbType;

        $result = $column->dbTypecast($value);

        if ($expected !== LobValue::class) {
            self::assertSame(
                $expected,
                $result,
                'Converted value must match.',
            );

            return;
        }

        self::assertInstanceOf(
            LobValue::class,
            $result,
            "BLOB string must yield an 'LobValue'.",
        );
        self::assertSame(
            'blob_col',
            $result->getColumnName(),
            'Target column name must be carried.',
        );
        self::assertSame(
            $value,
            $result->getValue(),
            'Original bytes must be preserved.',
        );
    }

    public function testDbTypecastBlobPdoValueLob(): void
    {
        $column = new ColumnSchema();

        $column->name = 'blob_col';
        $column->type = Schema::TYPE_BINARY;
        $column->dbType = 'BLOB';

        $result = $column->dbTypecast(new PdoValue('binary data', PDO::PARAM_LOB));

        self::assertInstanceOf(
            LobValue::class,
            $result,
            "LOB 'PdoValue' must yield an 'LobValue'.",
        );
        self::assertSame(
            'binary data',
            $result->getValue(),
            'Wrapped bytes must be preserved.',
        );
    }

    public function testDbTypecastBlobNonLobPdoValuePreserved(): void
    {
        $column = new ColumnSchema();

        $column->name = 'blob_col';
        $column->type = Schema::TYPE_BINARY;
        $column->dbType = 'BLOB';

        $value = new PdoValue('plain', PDO::PARAM_STR);

        self::assertSame(
            $value,
            $column->dbTypecast($value),
            'Non-LOB `PdoValue` must be preserved.',
        );
    }

    public function testDbTypecastBlobStreamResources(): void
    {
        $column = new ColumnSchema();
        $column->name = 'blob_col';
        $column->type = Schema::TYPE_BINARY;
        $column->dbType = 'BLOB';
        $stream = fopen('php://temp', 'w+b');

        self::assertIsResource($stream, 'Fixture stream must open.');

        try {
            $result = $column->dbTypecast($stream);

            self::assertInstanceOf(
                LobValue::class,
                $result,
                "Stream must yield an 'LobValue'.",
            );
            self::assertSame(
                $stream,
                $result->getValue(),
                'Stream resource must be carried unchanged.',
            );

            $result = $column->dbTypecast(new PdoValue($stream, PDO::PARAM_LOB));

            self::assertInstanceOf(
                LobValue::class,
                $result,
                "LOB 'PdoValue' stream must yield an 'LobValue'.",
            );
            self::assertSame(
                $stream,
                $result->getValue(),
                'Wrapped stream resource must be carried unchanged.',
            );
        } finally {
            fclose($stream);
        }
    }

    public function testDbTypecastBlobBinaryString(): void
    {
        $column = new ColumnSchema();

        $column->name = 'blob_col';
        $column->type = Schema::TYPE_BINARY;
        $column->dbType = 'BLOB';
        $value = "binary\0\xFF\xFE\x80data";

        $result = $column->dbTypecast($value);

        self::assertInstanceOf(
            LobValue::class,
            $result,
            "Binary string must yield an 'LobValue'.",
        );
        self::assertSame(
            $value,
            $result->getValue(),
            'NUL and non-text bytes must be preserved.',
        );
    }

    public function testDbTypecastBlobExpression(): void
    {
        $column = new ColumnSchema();

        $column->type = Schema::TYPE_BINARY;
        $column->dbType = 'BLOB';

        $expression = new Expression('EMPTY_BLOB()');

        self::assertSame(
            $expression,
            $column->dbTypecast($expression),
            'BLOB expressions must retain their existing behavior.',
        );
    }

    public function testDbTypecastBlobLobValuePreserved(): void
    {
        $column = new ColumnSchema();

        $column->name = 'blob_col';
        $column->type = Schema::TYPE_BINARY;
        $column->dbType = 'BLOB';

        $value = new LobValue('blob_col', 'payload');

        self::assertSame(
            $value,
            $column->dbTypecast($value),
            "An existing 'LobValue' must pass through unchanged.",
        );
    }

    public function testDbTypecastNonBlobColumnDoesNotWrapResource(): void
    {
        $column = new ColumnSchema();

        $column->name = 'char_col';
        $column->type = Schema::TYPE_STRING;
        $column->dbType = 'VARCHAR2';
        $column->phpType = 'string';

        $stream = fopen('php://temp', 'w+b');

        self::assertIsResource(
            $stream,
            'Fixture stream must open.',
        );
        self::assertNotInstanceOf(
            LobValue::class,
            $column->dbTypecast($stream),
            "A resource on a non-BLOB column must not become a 'LobValue'.",
        );

        fclose($stream);
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
