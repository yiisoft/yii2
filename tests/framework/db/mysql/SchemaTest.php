<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mysql;
use yii\db\Expression;

use yii\db\mysql\ColumnSchema;
use yii\db\mysql\Schema;
use yiiunit\framework\db\AnyCaseValue;

/**
 * @group db
 * @group mysql
 */
class SchemaTest extends \yiiunit\framework\db\SchemaTest
{
    public $driverName = 'mysql';

    public function testLoadDefaultDatetimeColumn()
    {
        if (!version_compare($this->getConnection()->pdo->getAttribute(\PDO::ATTR_SERVER_VERSION), '5.6', '>=')) {
            $this->markTestSkipped('Default datetime columns are supported since MySQL 5.6.');
        }
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

        $this->assertInstanceOf(Expression::className(), $dt->defaultValue);
        $this->assertEquals('CURRENT_TIMESTAMP', (string)$dt->defaultValue);
    }

    public function testDefaultDatetimeColumnWithMicrosecs()
    {
        if (!version_compare($this->getConnection()->pdo->getAttribute(\PDO::ATTR_SERVER_VERSION), '5.6.4', '>=')) {
            $this->markTestSkipped('CURRENT_TIMESTAMP with microseconds as default column value is supported since MySQL 5.6.4.');
        }
        $sql = <<<SQL
CREATE TABLE  IF NOT EXISTS `current_timestamp_test`  (
  `dt` datetime(2) NOT NULL DEFAULT CURRENT_TIMESTAMP(2),
  `ts` timestamp(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQL;

        $this->getConnection()->createCommand($sql)->execute();

        $schema = $this->getConnection()->getTableSchema('current_timestamp_test');

        $dt = $schema->columns['dt'];
        $this->assertInstanceOf(Expression::className(), $dt->defaultValue);
        $this->assertEquals('CURRENT_TIMESTAMP(2)', (string)$dt->defaultValue);

        $ts = $schema->columns['ts'];
        $this->assertInstanceOf(Expression::className(), $ts->defaultValue);
        $this->assertEquals('CURRENT_TIMESTAMP(3)', (string)$ts->defaultValue);
    }

    public function testGetSchemaNames()
    {
        $this->markTestSkipped('Schemas are not supported in MySQL.');
    }

    public function constraintsProvider()
    {
        $result = parent::constraintsProvider();
        $result['1: check'][2] = false;

        $result['2: primary key'][2]->name = null;
        $result['2: check'][2] = false;

        // Work aroung bug in MySQL 5.1 - it creates only this table in lowercase. O_o
        $result['3: foreign key'][2][0]->foreignTableName = new AnyCaseValue('T_constraints_2');
        $result['3: check'][2] = false;

        $result['4: check'][2] = false;
        return $result;
    }

    /**
     * When displayed in the INFORMATION_SCHEMA.COLUMNS table, a default CURRENT TIMESTAMP is displayed
     * as CURRENT_TIMESTAMP up until MariaDB 10.2.2, and as current_timestamp() from MariaDB 10.2.3.
     *
     * @see https://mariadb.com/kb/en/library/now/#description
     * @see https://github.com/yiisoft/yii2/issues/15167
     */
    public function testAlternativeDisplayOfDefaultCurrentTimestampInMariaDB()
    {
        /**
         * We do not have a real database MariaDB >= 10.2.3 for tests, so we emulate the information that database
         * returns in response to the query `SHOW FULL COLUMNS FROM ...`
         */
        $schema = new Schema();
        $this->setInaccessibleProperty($schema, 'db', $this->getConnection());
        // $this->setInaccessibleProperty($schema, '_serverVersion', 'MariaDB');
        // isMariaDb
        $column = $this->invokeMethod($schema, 'loadColumnSchema', [[
            'field' => 'emulated_MariaDB_field',
            'type' => 'timestamp',
            'collation' => NULL,
            'null' => 'NO',
            'key' => '',
            'default' => new Expression('current_timestamp()'),
            'extra' => '',
            'privileges' => 'select,insert,update,references',
            'comment' => '',
        ]]);

        $this->assertInstanceOf(ColumnSchema::className(), $column);
        $this->assertInstanceOf(Expression::className(), $column->defaultValue);
        $this->assertSame('current_timestamp()', (string)$column->defaultValue);
    }

    /**
     * When displayed in the INFORMATION_SCHEMA.COLUMNS table, a default CURRENT TIMESTAMP is provided
     * as NULL.
     *
     * @see https://github.com/yiisoft/yii2/issues/19047
     */
    public function testAlternativeDisplayOfDefaultCurrentTimestampAsNullInMariaDB()
    {
        $schema = new Schema();
        $this->setInaccessibleProperty($schema, 'db', $this->getConnection());
        $column = $this->invokeMethod($schema, 'loadColumnSchema', [[
            'field' => 'emulated_MariaDB_field',
            'type' => 'timestamp',
            'collation' => NULL,
            'null' => 'NO',
            'key' => '',
            'default' => NULL,
            'extra' => '',
            'privileges' => 'select,insert,update,references',
            'comment' => '',
        ]]);

        $this->assertInstanceOf(ColumnSchema::className(), $column);
        $this->assertEquals(NULL, $column->defaultValue);
    }

    public function getExpectedColumns()
    {
        $version = $this->getConnection()->getSchema()->getServerVersion();

        $columns = array_merge(
            parent::getExpectedColumns(),
            [
                'int_col' => [
                    'type' => 'integer',
                    'dbType' => \version_compare($version, '8.0.17', '>') ? 'int' : 'int(11)',
                    'phpType' => 'integer',
                    'allowNull' => false,
                    'autoIncrement' => false,
                    'enumValues' => null,
                    'size' => \version_compare($version, '8.0.17', '>') ? null : 11,
                    'precision' => \version_compare($version, '8.0.17', '>') ? null : 11,
                    'scale' => null,
                    'defaultValue' => null,
                ],
                'int_col2' => [
                    'type' => 'integer',
                    'dbType' => \version_compare($version, '8.0.17', '>') ? 'int' : 'int(11)',
                    'phpType' => 'integer',
                    'allowNull' => true,
                    'autoIncrement' => false,
                    'enumValues' => null,
                    'size' => \version_compare($version, '8.0.17', '>') ? null : 11,
                    'precision' => \version_compare($version, '8.0.17', '>') ? null : 11,
                    'scale' => null,
                    'defaultValue' => 1,
                ],
                'int_col3' => [
                    'type' => 'integer',
                    'dbType' => \version_compare($version, '8.0.17', '>') ? 'int unsigned' : 'int(11) unsigned',
                    'phpType' => 'integer',
                    'allowNull' => true,
                    'autoIncrement' => false,
                    'enumValues' => null,
                    'size' => \version_compare($version, '8.0.17', '>') ? null : 11,
                    'precision' => \version_compare($version, '8.0.17', '>') ? null : 11,
                    'scale' => null,
                    'defaultValue' => 1,
                ],
                'tinyint_col' => [
                    'type' => 'tinyint',
                    'dbType' => \version_compare($version, '8.0.17', '>') ? 'tinyint' : 'tinyint(3)',
                    'phpType' => 'integer',
                    'allowNull' => true,
                    'autoIncrement' => false,
                    'enumValues' => null,
                    'size' => \version_compare($version, '8.0.17', '>') ? null : 3,
                    'precision' => \version_compare($version, '8.0.17', '>') ? null : 3,
                    'scale' => null,
                    'defaultValue' => 1,
                ],
                'smallint_col' => [
                    'type' => 'smallint',
                    'dbType' =>  \version_compare($version, '8.0.17', '>') ? 'smallint' : 'smallint(1)',
                    'phpType' => 'integer',
                    'allowNull' => true,
                    'autoIncrement' => false,
                    'enumValues' => null,
                    'size' => \version_compare($version, '8.0.17', '>') ? null : 1,
                    'precision' => \version_compare($version, '8.0.17', '>') ? null : 1,
                    'scale' => null,
                    'defaultValue' => 1,
                ],
                'bigint_col' => [
                    'type' => 'bigint',
                    'dbType' => \version_compare($version, '8.0.17', '>') ? 'bigint unsigned' : 'bigint(20) unsigned',
                    'phpType' => 'string',
                    'allowNull' => true,
                    'autoIncrement' => false,
                    'enumValues' => null,
                    'size' => \version_compare($version, '8.0.17', '>') ? null : 20,
                    'precision' => \version_compare($version, '8.0.17', '>') ? null : 20,
                    'scale' => null,
                    'defaultValue' => null,
                ],
            ]
        );

        if (version_compare($version, '5.7', '<')) {
            $columns['int_col3']['phpType'] = 'string';

            $columns['json_col']['type'] = 'text';
            $columns['json_col']['dbType'] = 'longtext';
            $columns['json_col']['phpType'] = 'string';
        }

        return $columns;
    }

    /**
     *
     */
    public function testMariaDBDefaultConstantAndExpression()
    {
        $this->driverName = 'maria';
        parent::setUp();
        $this->assertFalse($this->getConnection()->getSchema()->isMysql());
        $this->assertTrue($this->getConnection()->getSchema()->isMariaDb());

        // Use MariaDB >= 10.8

        $sql = static::defaultForColumnTableSql();

        $this->getConnection()->createCommand($sql)->execute();
        $schema = $this->getConnection()->getTableSchema('default_test', true);

        $name = $schema->columns['name'];
        $this->assertNull($name->defaultValue);

        $fatherName = $schema->columns['father_name'];
        $this->assertSame($fatherName->defaultValue, 'NULL');

        $nn2 = $schema->columns['nn2'];
        $this->assertSame($nn2->defaultValue, "NULL");

        $nn3 = $schema->columns['nn3'];
        $this->assertNull($nn3->defaultValue);

        $nn4 = $schema->columns['nn4'];
        $this->assertSame($nn4->defaultValue, 'the default value');

        $lastName = $schema->columns['last_name'];
        $this->assertNull($lastName->defaultValue);

        $dt = $schema->columns['dt'];
        $this->assertInstanceOf(Expression::className(), $dt->defaultValue);
        $this->assertEquals('current_timestamp()', (string)$dt->defaultValue);

        $dt2 = $schema->columns['dt2'];
        $this->assertEquals('2011-11-11 00:00:00', $dt2->defaultValue);

        $ts = $schema->columns['ts'];
        $this->assertInstanceOf(Expression::className(), $ts->defaultValue);
        $this->assertEquals('current_timestamp()', (string)$ts->defaultValue);

        $ts2 = $schema->columns['ts2'];
        $this->assertEquals('2011-11-11 00:00:00', $ts2->defaultValue);

        $dateCol = $schema->columns['date_col'];
        $this->assertInstanceOf(Expression::className(), $dateCol->defaultValue);
        $this->assertEquals('(curdate() + interval 2 year)', (string)$dateCol->defaultValue);

        $i = $schema->columns['i'];
        $this->assertEquals(-1, $i->defaultValue);

        $c = $schema->columns['c'];
        $this->assertEquals('', $c->defaultValue);

        $price = $schema->columns['price'];
        $this->assertEquals(0.00, $price->defaultValue);

        $i2 = $schema->columns['i2'];
        $this->assertEquals(0, $i2->defaultValue);

        $i3 = $schema->columns['i3'];
        $this->assertEquals(3, $i3->defaultValue);

        $pi_val = $schema->columns['pi_val'];
        $this->assertEquals(3.14, $pi_val->defaultValue);

        $f = $schema->columns['f'];
        $this->assertInstanceOf(Expression::className(), $f->defaultValue);
        $this->assertEquals('(rand() * rand())', (string)$f->defaultValue);

        $d = $schema->columns['d'];
        $this->assertInstanceOf(Expression::className(), $d->defaultValue);
        $this->assertEquals('(curdate() + interval 1 year)', (string)$d->defaultValue);

        $p = $schema->columns['p'];
        $this->assertInstanceOf(Expression::className(), $p->defaultValue);
        $this->assertEquals('point(0,0)', (string)$p->defaultValue);

        $j = $schema->columns['j'];
        $this->assertInstanceOf(Expression::className(), $j->defaultValue);
        $this->assertEquals('json_array()', (string)$j->defaultValue);

        $b = $schema->columns['b'];
        $this->assertEquals("'abc'", $b->defaultValue);

        $this->getConnection()->createCommand('DROP TABLE IF EXISTS default_test')->execute();
    }

    public function testMysqlDefaultConstantAndExpression()
    {
        if (!version_compare($this->getConnection()->pdo->getAttribute(\PDO::ATTR_SERVER_VERSION), '8', '>=')) {
            $this->markTestSkipped('Default expression for columns are supported since MySQL 8.');
        }

        $this->assertTrue($this->getConnection()->getSchema()->isMysql());
        $this->assertFalse($this->getConnection()->getSchema()->isMariaDb());

        $sql = static::defaultForColumnTableSql();

        $this->getConnection()->createCommand($sql)->execute();
        $schema = $this->getConnection()->getTableSchema('default_test', true);

        $name = $schema->columns['name'];
        $this->assertNull($name->defaultValue);

        $fatherName = $schema->columns['father_name'];
        $this->assertSame($fatherName->defaultValue, 'NULL');

        $nn2 = $schema->columns['nn2'];
        $this->assertInstanceOf(Expression::className(), $nn2->defaultValue);
        $this->assertSame((string)$nn2->defaultValue, "_utf8mb4\'NULL\'");

        $nn3 = $schema->columns['nn3'];
        $this->assertInstanceOf(Expression::className(), $nn3->defaultValue);
        $this->assertSame((string)$nn3->defaultValue, "NULL");

        $nn4 = $schema->columns['nn4'];
        $this->assertSame($nn4->defaultValue, 'the default value');

        $lastName = $schema->columns['last_name'];
        $this->assertNull($lastName->defaultValue);

        $dt = $schema->columns['dt'];
        $this->assertInstanceOf(Expression::className(), $dt->defaultValue);
        $this->assertEquals('CURRENT_TIMESTAMP', (string)$dt->defaultValue);

        $dt2 = $schema->columns['dt2'];
        $this->assertEquals('2011-11-11 00:00:00', $dt2->defaultValue);

        $ts = $schema->columns['ts'];
        $this->assertInstanceOf(Expression::className(), $ts->defaultValue);
        $this->assertEquals('CURRENT_TIMESTAMP', (string)$ts->defaultValue);

        $ts2 = $schema->columns['ts2'];
        $this->assertEquals('2011-11-11 00:00:00', $ts2->defaultValue);

        $dateCol = $schema->columns['date_col'];
        $this->assertInstanceOf(Expression::className(), $dateCol->defaultValue);
        $this->assertEquals('(curdate() + interval 2 year)', (string)$dateCol->defaultValue);

        $i = $schema->columns['i'];
        $this->assertEquals(-1, $i->defaultValue);

        $c = $schema->columns['c'];
        $this->assertEquals('', $c->defaultValue);

        $price = $schema->columns['price'];
        $this->assertEquals(0.00, $price->defaultValue);

        $i2 = $schema->columns['i2'];
        $this->assertEquals(0, $i2->defaultValue);

        $i3 = $schema->columns['i3'];
        $this->assertEquals(3, $i3->defaultValue);

        $pi_val = $schema->columns['pi_val'];
        $this->assertEquals(3.14, $pi_val->defaultValue);

        $f = $schema->columns['f'];
        $this->assertInstanceOf(Expression::className(), $f->defaultValue);
        $this->assertEquals('(rand() * rand())', (string)$f->defaultValue);

        $d = $schema->columns['d'];
        $this->assertInstanceOf(Expression::className(), $d->defaultValue);
        $this->assertEquals('(curdate() + interval 1 year)', (string)$d->defaultValue);

        $p = $schema->columns['p'];
        $this->assertInstanceOf(Expression::className(), $p->defaultValue);
        $this->assertEquals('point(0,0)', (string)$p->defaultValue);

        $j = $schema->columns['j'];
        $this->assertInstanceOf(Expression::className(), $j->defaultValue);
        $this->assertEquals('json_array()', (string)$j->defaultValue);

        $b = $schema->columns['b'];
        $this->assertInstanceOf(Expression::className(), $b->defaultValue);
        $this->assertEquals("_utf8mb4\'abc\'", $b->defaultValue);

        $this->getConnection()->createCommand('DROP TABLE IF EXISTS default_test')->execute();
    }

    private static function defaultForColumnTableSql()
    {
        $sql = <<<SQL
            CREATE TABLE  IF NOT EXISTS `default_test`  (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(255) NOT NULL,
              `father_name` varchar(255) DEFAULT 'NULL',
              `nn2` varchar(255) DEFAULT ('NULL'),
              `nn3` varchar(255) DEFAULT (null),
              `nn4` varchar(255) DEFAULT 'the default value',
              `last_name` varchar(255) NULL,
              `dt` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `dt2` datetime NOT NULL DEFAULT '2011-11-11 00:00:00',
              `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              `ts2` timestamp NOT NULL DEFAULT '2011-11-11 00:00:00',
              `date_col` date DEFAULT (CURRENT_DATE + INTERVAL 2 YEAR),
               i     INT DEFAULT -1,
               c     VARCHAR(10) DEFAULT '',
               price DOUBLE(16,2) DEFAULT 0.00,
               -- literal defaults
               i2 INT         DEFAULT 0,
               i3 INT         DEFAULT 3,
               pi_val FLOAT         DEFAULT 3.14,
               -- expression defaults
               f FLOAT       DEFAULT (RAND() * RAND()),
               d DATE        DEFAULT (CURRENT_DATE + INTERVAL 1 YEAR),
               p POINT       DEFAULT (Point(0,0)),
               j JSON        DEFAULT (JSON_ARRAY()),
               b BLOB DEFAULT ('abc'),

               PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8
SQL;

        return $sql;
    }
}
