<?php

declare(strict_types=1);

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mysql;

use PDO;
use yii\db\Expression;
use yii\db\mysql\ColumnSchema;
use yii\db\mysql\Schema;
use yiiunit\framework\db\AnyCaseValue;
use yiiunit\base\db\BaseSchema;

use function stripos;

/**
 * Unit test for {@see \yii\db\mysql\Schema} class.
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

        $this->assertInstanceOf(Expression::class, $dt->defaultValue);
        $this->assertEquals('CURRENT_TIMESTAMP', (string)$dt->defaultValue);
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
        $this->assertInstanceOf(Expression::class, $dt->defaultValue);
        $this->assertEquals('CURRENT_TIMESTAMP(2)', (string)$dt->defaultValue);

        $ts = $schema->columns['ts'];
        $this->assertInstanceOf(Expression::class, $ts->defaultValue);
        $this->assertEquals('CURRENT_TIMESTAMP(3)', (string)$ts->defaultValue);
    }

    public function testGetSchemaNames(): void
    {
        $this->markTestSkipped('Schemas are not supported in MySQL.');
    }

    public static function constraintsProvider(): array
    {
        $result = parent::constraintsProvider();

        $result['1: check'][2][0]->columnNames = null;
        $result['1: check'][2][0]->expression = "`C_check` <> ''";
        $result['2: primary key'][2]->name = null;

        $result['3: foreign key'][2][0]->foreignTableName = new AnyCaseValue('T_constraints_2');

        return $result;
    }

    /**
     * @dataProvider constraintsProvider
     * @param string $tableName
     * @param string $type
     * @param mixed $expected
     */
    public function testTableSchemaConstraints($tableName, $type, $expected): void
    {
        $version = $this->getConnection(false)->getServerVersion();

        if ($expected === false) {
            $this->expectException('yii\base\NotSupportedException');
        }

        if (
            $this->driverName === 'mysql' &&
            \stripos($version, 'MariaDb') === false &&
            version_compare($version, '8.0.16', '<') &&
            $type === 'checks'
        ) {
            $this->expectException('yii\base\NotSupportedException');
        } elseif (
            $this->driverName === 'mysql' &&
            \stripos($version, 'MariaDb') === false &&
            version_compare($version, '8.0.16', '>=') &&
            $tableName === 'T_constraints_1' &&
            $type === 'checks'
        ) {
            $expected[0]->expression = "(`C_check` <> _utf8mb4\\'\\')";
        }

        $constraints = $this->getConnection(false)->getSchema()->{'getTable' . ucfirst($type)}($tableName);
        $this->assertMetadataEquals($expected, $constraints);
    }

    /**
     * @dataProvider uppercaseConstraintsProvider
     * @param string $tableName
     * @param string $type
     * @param mixed $expected
     */
    public function testTableSchemaConstraintsWithPdoUppercase($tableName, $type, $expected): void
    {
        $version = $this->getConnection(false)->getServerVersion();

        if ($expected === false) {
            $this->expectException('yii\base\NotSupportedException');
        }

        if (
            $this->driverName === 'mysql' &&
            \stripos($version, 'MariaDb') === false &&
            version_compare($version, '8.0.16', '<') &&
            $type === 'checks'
        ) {
            $this->expectException('yii\base\NotSupportedException');
        } elseif (
            $this->driverName === 'mysql' &&
            \stripos($version, 'MariaDb') === false &&
            version_compare($version, '8.0.16', '>=') &&
            $tableName === 'T_constraints_1' &&
            $type === 'checks'
        ) {
            $expected[0]->expression = "(`C_check` <> _utf8mb4\\'\\')";
        }

        $connection = $this->getConnection(false);
        $connection->getSlavePdo(true)->setAttribute(PDO::ATTR_CASE, PDO::CASE_UPPER);
        $constraints = $connection->getSchema()->{'getTable' . ucfirst($type)}($tableName, true);
        $this->assertMetadataEquals($expected, $constraints);
    }

    /**
     * @dataProvider lowercaseConstraintsProvider
     * @param string $tableName
     * @param string $type
     * @param mixed $expected
     */
    public function testTableSchemaConstraintsWithPdoLowercase($tableName, $type, $expected): void
    {
        $version = $this->getConnection(false)->getServerVersion();

        if ($expected === false) {
            $this->expectException('yii\base\NotSupportedException');
        }

        if (
            $this->driverName === 'mysql' &&
            \stripos($version, 'MariaDb') === false &&
            version_compare($version, '8.0.16', '<') &&
            $type === 'checks'
        ) {
            $this->expectException('yii\base\NotSupportedException');
        } elseif (
            $this->driverName === 'mysql' &&
            \stripos($version, 'MariaDb') === false &&
            version_compare($version, '8.0.16', '>=') &&
            $tableName === 'T_constraints_1' &&
            $type === 'checks'
        ) {
            $expected[0]->expression = "(`C_check` <> _utf8mb4\\'\\')";
        }

        $connection = $this->getConnection(false);
        $connection->getSlavePdo(true)->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
        $constraints = $connection->getSchema()->{'getTable' . ucfirst($type)}($tableName, true);
        $this->assertMetadataEquals($expected, $constraints);
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
        $column = $this->invokeMethod($schema, 'loadColumnSchema', [[
            'field' => 'emulated_MariaDB_field',
            'type' => 'timestamp',
            'collation' => null,
            'null' => 'NO',
            'key' => '',
            'default' => 'current_timestamp()',
            'extra' => '',
            'privileges' => 'select,insert,update,references',
            'comment' => '',
        ]]);

        $this->assertInstanceOf(ColumnSchema::class, $column);
        $this->assertInstanceOf(Expression::class, $column->defaultValue);
        $this->assertEquals('CURRENT_TIMESTAMP', $column->defaultValue);
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
        $column = $this->invokeMethod($schema, 'loadColumnSchema', [[
            'field' => 'emulated_MariaDB_field',
            'type' => 'timestamp',
            'collation' => null,
            'null' => 'NO',
            'key' => '',
            'default' => null,
            'extra' => '',
            'privileges' => 'select,insert,update,references',
            'comment' => '',
        ]]);

        $this->assertInstanceOf(ColumnSchema::class, $column);
        $this->assertEquals(null, $column->defaultValue);
    }

    public function getExpectedColumns()
    {
        $version = $this->getConnection(false)->getServerVersion();

        $columns = [
            ...parent::getExpectedColumns(),
            'int_col' => [
                    'type' => 'integer',
                    'dbType' => 'int',
                    'phpType' => 'integer',
                    'allowNull' => false,
                    'autoIncrement' => false,
                    'enumValues' => null,
                    'size' => null,
                    'precision' => null,
                    'scale' => null,
                    'defaultValue' => null,
                ],
                'int_col2' => [
                    'type' => 'integer',
                    'dbType' => 'int',
                    'phpType' => 'integer',
                    'allowNull' => true,
                    'autoIncrement' => false,
                    'enumValues' => null,
                    'size' => null,
                    'precision' => null,
                    'scale' => null,
                    'defaultValue' => 1,
                ],
                'int_col3' => [
                    'type' => 'integer',
                    'dbType' => 'int unsigned',
                    'phpType' => 'integer',
                    'allowNull' => true,
                    'autoIncrement' => false,
                    'enumValues' => null,
                    'size' => null,
                    'precision' => null,
                    'scale' => null,
                    'defaultValue' => 1,
                ],
                'tinyint_col' => [
                    'type' => 'tinyint',
                    'dbType' => 'tinyint',
                    'phpType' => 'integer',
                    'allowNull' => true,
                    'autoIncrement' => false,
                    'enumValues' => null,
                    'size' => null,
                    'precision' => null,
                    'scale' => null,
                    'defaultValue' => 1,
                ],
                'smallint_col' => [
                    'type' => 'smallint',
                    'dbType' => 'smallint',
                    'phpType' => 'integer',
                    'allowNull' => true,
                    'autoIncrement' => false,
                    'enumValues' => null,
                    'size' => null,
                    'precision' => null,
                    'scale' => null,
                    'defaultValue' => 1,
                ],
                'bigint_col' => [
                    'type' => 'bigint',
                    'dbType' => 'bigint unsigned',
                    'phpType' => 'string',
                    'allowNull' => true,
                    'autoIncrement' => false,
                    'enumValues' => null,
                    'size' => null,
                    'precision' => null,
                    'scale' => null,
                    'defaultValue' => null,
                ]
        ];

        if (stripos($version, 'MariaDb') !== false) {
            $mariaDbOverrides = [
                'int_col' => ['int(11)', 11],
                'int_col2' => ['int(11)', 11],
                'int_col3' => ['int(11) unsigned', 11],
                'tinyint_col' => ['tinyint(3)', 3],
                'smallint_col' => ['smallint(1)', 1],
                'bigint_col' => ['bigint(20) unsigned', 20],
            ];

            foreach ($mariaDbOverrides as $col => [$dbType, $size]) {
                $columns[$col]['dbType'] = $dbType;
                $columns[$col]['size'] = $size;
                $columns[$col]['precision'] = $size;
            }
        }

        return $columns;
    }
}
