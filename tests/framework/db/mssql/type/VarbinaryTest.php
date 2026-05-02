<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql\type;

use PHPUnit\Framework\Attributes\DataProviderExternal;
use PHPUnit\Framework\Attributes\Group;
use yii\db\Expression;
use yii\db\Query;
use yii\db\Schema;
use yii\db\mssql\ColumnSchema;
use yiiunit\framework\db\DatabaseTestCase;
use yiiunit\framework\db\mssql\type\providers\VarbinaryProvider;

/**
 * Unit and integration tests for the MSSQL `varbinary` type.
 *
 * {@see VarbinaryProvider} for test case data providers.
 *
 * @author Wilmer Arambula <terabytesoftw@gmail.com>
 * @since 22.0
 */
#[Group('db')]
#[Group('mssql')]
#[Group('column')]
final class VarbinaryTest extends DatabaseTestCase
{
    protected $driverName = 'sqlsrv';

    public function testDbTypecastReturnsCastExpressionForNullOnNullableVarbinary(): void
    {
        $column = $this->makeVarbinary(true);

        $result = $column->dbTypecast(null);

        self::assertInstanceOf(
            Expression::class,
            $result,
            'Nullable column must yield CAST expression.',
        );
        self::assertSame(
            'CAST(NULL AS VARBINARY(MAX))',
            (string) $result,
            'SQL must be CAST NULL form.',
        );
    }

    public function testDbTypecastDelegatesToParentForNullOnNonNullableVarbinary(): void
    {
        $column = $this->makeVarbinary(false);

        $result = $column->dbTypecast(null);

        self::assertNull(
            $result,
            'Non-nullable null must pass through unchanged.',
        );
    }

    public function testDbTypecastDelegatesToParentForNonVarbinaryDbType(): void
    {
        $column = new ColumnSchema();

        $column->type = Schema::TYPE_BINARY;

        $column->phpType = 'resource';
        $column->dbType = 'binary';

        $result = $column->dbTypecast('hello');

        self::assertNotInstanceOf(
            Expression::class,
            $result,
            "'binary' dbType must not be wrapped.",
        );
    }

    public function testDbTypecastDelegatesToParentForNonBinaryType(): void
    {
        $column = new ColumnSchema();

        $column->type = Schema::TYPE_STRING;

        $column->phpType = 'string';
        $column->dbType = 'varchar';

        $result = $column->dbTypecast('hello');

        self::assertNotInstanceOf(
            Expression::class,
            $result,
            'Non-binary type must not be wrapped.',
        );
    }

    #[DataProviderExternal(VarbinaryProvider::class, 'varbinaryStringValue')]
    public function testDbTypecastSqlMatchesLegacyNormalizeTableRowDataFormat(string $value): void
    {
        $column = $this->makeVarbinary(true);

        $expected = 'CONVERT(VARBINARY(MAX), ' . ('0x' . bin2hex($value)) . ')';

        $actual = (string) $column->dbTypecast($value);

        self::assertSame(
            $expected,
            $actual,
            'New SQL must match legacy normalizeTableRowData output.',
        );
    }

    public function testVarbinary(): void
    {
        $db = $this->getConnection();

        $db->createCommand()->delete('type')->execute();
        $db->createCommand()->insert(
            'type',
            [
                'int_col' => $key = 1,
                'char_col' => '',
                'char_col2' => '6a3ce1a0bffe8eeb6fa986caf443e24c',
                'float_col' => 0.0,
                'blob_col' => 'a:1:{s:13:"template";s:1:"1";}',
                'bool_col' => true,
            ],
        )->execute();

        $result = (new Query())
            ->select(['blob_col'])
            ->from('type')
            ->where(['int_col' => $key])
            ->createCommand($db)
            ->queryScalar();

        self::assertSame(
            'a:1:{s:13:"template";s:1:"1";}',
            $result,
            'Round-trip must preserve serialized payload.',
        );
    }

    private function makeVarbinary(bool $allowNull): ColumnSchema
    {
        $column = new ColumnSchema();

        $column->type = Schema::TYPE_BINARY;

        $column->phpType = 'resource';
        $column->dbType = 'varbinary';
        $column->allowNull = $allowNull;

        return $column;
    }
}
