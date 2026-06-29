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
use yii\db\Expression;
use yii\db\mssql\ColumnSchema;
use yii\db\mssql\Schema;
use yiiunit\base\db\BaseColumnSchema;
use yiiunit\framework\db\mssql\providers\ColumnSchemaProvider;

use function fclose;
use function fopen;
use function fwrite;
use function hex2bin;
use function rewind;

/**
 * Unit tests for {@see \yii\db\mssql\ColumnSchema} type-casting and OUTPUT column declarations for the MSSQL driver.
 *
 * {@see ColumnSchemaProvider} for test case data providers.
 */
#[Group('db')]
#[Group('mssql')]
#[Group('column-schema')]
final class ColumnSchemaTest extends BaseColumnSchema
{
    protected $driverName = 'sqlsrv';

    /**
     * @param array<string, array<string, mixed>> $columns Expected column metadata.
     */
    #[DataProviderExternal(ColumnSchemaProvider::class, 'columnSchema')]
    public function testColumnSchema(array $columns): void
    {
        parent::testColumnSchema($columns);
    }

    #[DataProviderExternal(ColumnSchemaProvider::class, 'dbTypecast')]
    public function testDbTypecast(
        string $type,
        string $dbType,
        bool $allowNull,
        mixed $value,
        mixed $expected,
    ): void {
        $column = new ColumnSchema();

        $column->type = $type;
        $column->dbType = $dbType;
        $column->allowNull = $allowNull;

        $result = $column->dbTypecast($value);

        if (!$expected instanceof Expression) {
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
            'Value must yield an Expression.',
        );
        self::assertSame(
            $expected->expression,
            $result->expression,
            'Expression SQL must match.',
        );
    }

    #[DataProviderExternal(ColumnSchemaProvider::class, 'defaultPhpTypecast')]
    public function testDefaultPhpTypecast(string $type, mixed $value, mixed $expected): void
    {
        $column = new ColumnSchema();

        $column->type = $type;
        $column->phpType = match ($type) {
            'integer' => 'integer',
            default => 'string',
        };

        self::assertSame(
            $expected,
            $column->defaultPhpTypecast($value),
            'Converted default must match.',
        );
    }

    public function testPhpTypecastVarbinaryStreamReturnsString(): void
    {
        $column = new ColumnSchema();

        $column->type = Schema::TYPE_BINARY;
        $column->dbType = 'varbinary(max)';
        $column->phpType = 'resource';

        $stream = fopen('php://memory', 'r+');

        fwrite($stream, 'binary data');
        rewind($stream);

        $result = $column->phpTypecast($stream);

        fclose($stream);

        self::assertSame(
            'binary data',
            $result,
            'Varbinary streams must be converted to strings.',
        );
    }

    public function testPhpTypecastRowVersionStreamReturnsInteger(): void
    {
        $column = new ColumnSchema();

        $column->type = Schema::TYPE_TIMESTAMP;

        $stream = fopen('php://memory', 'r+');

        fwrite($stream, hex2bin('00000000000012e9'));
        rewind($stream);

        $result = $column->phpTypecast($stream);

        fclose($stream);

        self::assertSame(
            4841,
            $result,
            'Rowversion streams must be decoded to the integer token.',
        );
    }

    #[DataProviderExternal(ColumnSchemaProvider::class, 'getOutputColumnDeclaration')]
    public function testGetOutputColumnDeclaration(
        string $dbType,
        bool $allowNull,
        int|null $size,
        string $expected,
    ): void {
        $column = new ColumnSchema();

        $column->dbType = $dbType;
        $column->allowNull = $allowNull;
        $column->size = $size;

        self::assertSame(
            $expected,
            $column->getOutputColumnDeclaration(),
            'Type declaration must match.',
        );
    }
}
