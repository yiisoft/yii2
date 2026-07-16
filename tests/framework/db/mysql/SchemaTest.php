<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mysql;

use PHPUnit\Framework\Attributes\Group;
use yii\base\NotSupportedException;
use yii\db\ConstraintFinderInterface;
use yii\db\Expression;
use yii\db\TableSchema;
use yii\db\mysql\ColumnSchema;
use yii\db\mysql\Schema;
use yiiunit\base\db\BaseSchema;
use yiiunit\support\DbHelper;

/**
 * Unit test for {@see \yii\db\mysql\Schema} schema reflection and metadata retrieval for the MySQL driver.
 */
#[Group('db')]
#[Group('mysql')]
#[Group('schema')]
final class SchemaTest extends BaseSchema
{
    public $driverName = 'mysql';

    public function testLoadDefaultDatetimeColumn(): void
    {
        $sql = <<<SQL
        CREATE TABLE  IF NOT EXISTS `datetime_test`  (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `dt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        SQL;

        $this->getConnection()->createCommand($sql)->execute();

        $schema = $this->getConnection()->getTableSchema('datetime_test');

        $dt = $schema->columns['dt'];

        self::assertInstanceOf(
            Expression::class,
            $dt->defaultValue,
            "Default value of 'CURRENT_TIMESTAMP' must be an Expression.",
        );
        self::assertSame(
            'CURRENT_TIMESTAMP',
            (string) $dt->defaultValue,
            "Expression must normalize to 'CURRENT_TIMESTAMP'.",
        );
    }

    public function testDefaultDatetimeColumnWithMicrosecs(): void
    {
        $sql = <<<SQL
        CREATE TABLE  IF NOT EXISTS `current_timestamp_test`  (
            `dt` datetime(2) NOT NULL DEFAULT CURRENT_TIMESTAMP(2),
            `ts` timestamp(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        SQL;

        $this->getConnection()->createCommand($sql)->execute();

        $schema = $this->getConnection()->getTableSchema('current_timestamp_test');

        $dt = $schema->columns['dt'];

        self::assertInstanceOf(
            Expression::class,
            $dt->defaultValue,
            "Default value of 'CURRENT_TIMESTAMP(2)' must be an Expression.",
        );
        self::assertSame(
            'CURRENT_TIMESTAMP(2)',
            (string) $dt->defaultValue,
            "Expression must normalize to 'CURRENT_TIMESTAMP(2)'.",
        );

        $ts = $schema->columns['ts'];

        self::assertInstanceOf(
            Expression::class,
            $ts->defaultValue,
            "Default value of 'CURRENT_TIMESTAMP(3)' must be an Expression.",
        );
        self::assertSame(
            'CURRENT_TIMESTAMP(3)',
            (string) $ts->defaultValue,
            "Expression must normalize to 'CURRENT_TIMESTAMP(3)'.",
        );
    }

    public function testLoadBitDefaultColumn(): void
    {
        $db = $this->getConnection();

        DbHelper::dropTablesIfExist($db, ['bit_default_test']);

        $db->createCommand()->createTable(
            'bit_default_test',
            [
                'nullable_bit1' => 'bit(1) NULL DEFAULT NULL',
                'nullable_bit8' => 'bit(8) NULL DEFAULT NULL',
                'bit1' => "bit(1) NOT NULL DEFAULT b'1'",
                'bit5' => "bit(5) NOT NULL DEFAULT b'10101'",
                'bit8' => "bit(8) NOT NULL DEFAULT b'10000010'",
            ],
            'ENGINE=InnoDB DEFAULT CHARSET=utf8',
        )->execute();

        $schema = $db->getTableSchema('bit_default_test', true);

        self::assertNull(
            $schema->columns['nullable_bit1']->defaultValue,
            "Nullable 'bit(1) DEFAULT NULL' must remain 'null'.",
        );
        self::assertNull(
            $schema->columns['nullable_bit8']->defaultValue,
            "Nullable 'bit(8) DEFAULT NULL' must remain 'null'.",
        );
        self::assertSame(
            1,
            $schema->columns['bit1']->defaultValue,
            "'bit(1)' must decode to '1'.",
        );
        self::assertSame(
            21,
            $schema->columns['bit5']->defaultValue,
            "'bit(5)' must decode to '21'.",
        );
        self::assertSame(
            130,
            $schema->columns['bit8']->defaultValue,
            "'bit(8)' must decode to '130'.",
        );
    }

    public function testGetSchemaNames(): void
    {
        $this->expectException(NotSupportedException::class);
        $this->expectExceptionMessage(
            Schema::class . ' does not support fetching all schema names.',
        );

        $this->getConnection()->getSchema()->getSchemaNames();
    }

    public function testResolveAndFindTableNamesWithDatabaseName(): void
    {
        $db = $this->getConnection();

        $databaseName = (string) $db->createCommand('SELECT DATABASE()')->queryScalar();
        $schema = $db->getSchema();

        self::assertInstanceOf(
            ConstraintFinderInterface::class,
            $schema,
            'Schema should support constraint metadata retrieval.',
        );

        self::assertContains(
            'profile',
            $schema->getTableNames($databaseName, true),
            "Table 'profile' should be present when listing tables with an explicit database name.",
        );
        self::assertSame(
            ['id'],
            $schema->getTablePrimaryKey("`{$databaseName}`.`profile`", true)->columnNames,
            'Primary key metadata should be reflected with an explicit database name.',
        );

        $table = $schema->getTableSchema("`{$databaseName}`.`profile`", true);

        self::assertInstanceOf(
            TableSchema::class,
            $table,
            'Table schema should be loadable with an explicit database name.',
        );
        self::assertSame(
            $databaseName,
            $table->schemaName,
            'Loaded table schema should keep the explicit database name.',
        );
        self::assertSame(
            'profile',
            $table->name,
            'Loaded table name should match expected value.',
        );
    }

    /**
     * When displayed in the INFORMATION_SCHEMA.COLUMNS table, a default CURRENT TIMESTAMP is displayed
     * as CURRENT_TIMESTAMP up until MariaDB 10.2.2, and as current_timestamp() from MariaDB 10.2.3.
     *
     * @see https://mariadb.com/kb/en/library/now/#description
     * @see https://github.com/yiisoft/yii2/issues/15167
     */
    public function testAlternativeDisplayOfDefaultCurrentTimestampInMariaDB(): void
    {
        /**
         * We do not have a real database MariaDB >= 10.2.3 for tests, so we emulate the information that database
         * returns in response to the query `SHOW FULL COLUMNS FROM ...`
         */
        $schema = new Schema();

        $column = $this->invokeMethod(
            $schema,
            'loadColumnSchema',
            [
                [
                    'field' => 'emulated_MariaDB_field',
                    'type' => 'timestamp',
                    'collation' => null,
                    'null' => 'NO',
                    'key' => '',
                    'default' => 'current_timestamp()',
                    'extra' => '',
                    'privileges' => 'select,insert,update,references',
                    'comment' => '',
                ],
            ],
        );

        $column->defaultValue = $column->defaultPhpTypecast($column->defaultValue);

        self::assertInstanceOf(
            ColumnSchema::class,
            $column,
            'Loaded column must be a MariaDB/MySQL ColumnSchema.',
        );
        self::assertInstanceOf(
            Expression::class,
            $column->defaultValue,
            'MariaDB `current_timestamp()` must yield an Expression.',
        );
        self::assertSame(
            'CURRENT_TIMESTAMP',
            (string) $column->defaultValue,
            'Expression must normalize to CURRENT_TIMESTAMP.',
        );
    }

    /**
     * When displayed in the INFORMATION_SCHEMA.COLUMNS table, a default CURRENT TIMESTAMP is provided
     * as NULL.
     *
     * @see https://github.com/yiisoft/yii2/issues/19047
     */
    public function testAlternativeDisplayOfDefaultCurrentTimestampAsNullInMariaDB(): void
    {
        $schema = new Schema();

        $column = $this->invokeMethod(
            $schema,
            'loadColumnSchema',
            [
                [
                    'field' => 'emulated_MariaDB_field',
                    'type' => 'timestamp',
                    'collation' => null,
                    'null' => 'NO',
                    'key' => '',
                    'default' => null,
                    'extra' => '',
                    'privileges' => 'select,insert,update,references',
                    'comment' => '',
                ],
            ],
        );

        $column->defaultValue = $column->defaultPhpTypecast($column->defaultValue);

        self::assertInstanceOf(
            ColumnSchema::class,
            $column,
            'Loaded column must be a MariaDB/MySQL ColumnSchema.',
        );
        self::assertSame(
            null,
            $column->defaultValue,
            "Default must remain 'null'.",
        );
    }

    public function testLoadDefaultCurrentTimestamp(): void
    {
        $sql = <<<SQL
        CREATE TABLE IF NOT EXISTS `current_timestamp_default_test` (
            `dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        SQL;

        $this->getConnection()->createCommand($sql)->execute();

        $column = $this->getConnection()->getTableSchema('current_timestamp_default_test', true)->columns['dt'];

        self::assertInstanceOf(
            Expression::class,
            $column->defaultValue,
            'CURRENT_TIMESTAMP default must yield an Expression.',
        );
        self::assertSame(
            'CURRENT_TIMESTAMP',
            (string) $column->defaultValue,
            'CURRENT_TIMESTAMP default expression must be normalized.',
        );
    }
}
