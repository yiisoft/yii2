<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db;

use yii\caching\FileCache;
use yii\db\Connection;
use yii\db\DataReader;
use yii\db\Expression;
use yii\db\Schema;

abstract class CommandTest extends DatabaseTestCase
{
    public function testConstruct()
    {
        $db = $this->getConnection(false);

        // null
        $command = $db->createCommand();
        $this->assertNull($command->sql);

        // string
        $sql = 'SELECT * FROM customer';
        $command = $db->createCommand($sql);
        $this->assertEquals($sql, $command->sql);
    }

    public function testGetSetSql()
    {
        $db = $this->getConnection(false);

        $sql = 'SELECT * FROM customer';
        $command = $db->createCommand($sql);
        $this->assertEquals($sql, $command->sql);

        $sql2 = 'SELECT * FROM order';
        $command->sql = $sql2;
        $this->assertEquals($sql2, $command->sql);
    }

    public function testAutoQuoting()
    {
        $db = $this->getConnection(false);

        $sql = 'SELECT [[id]], [[t.name]] FROM {{customer}} t';
        $command = $db->createCommand($sql);
        $this->assertEquals('SELECT `id`, `t`.`name` FROM `customer` t', $command->sql);
    }

    public function testPrepareCancel()
    {
        $db = $this->getConnection(false);

        $command = $db->createCommand('SELECT * FROM {{customer}}');
        $this->assertEquals(null, $command->pdoStatement);
        $command->prepare();
        $this->assertNotNull($command->pdoStatement);
        $command->cancel();
        $this->assertEquals(null, $command->pdoStatement);
    }

    public function testExecute()
    {
        $db = $this->getConnection();

        $sql = 'INSERT INTO {{customer}}([[email]], [[name]], [[address]]) VALUES (\'user4@example.com\', \'user4\', \'address4\')';
        $command = $db->createCommand($sql);
        $this->assertEquals(1, $command->execute());

        $sql = 'SELECT COUNT(*) FROM {{customer}} WHERE [[name]] = \'user4\'';
        $command = $db->createCommand($sql);
        $this->assertEquals(1, $command->queryScalar());

        $command = $db->createCommand('bad SQL');
        $this->expectException('\yii\db\Exception');
        $command->execute();
    }

    public function testQuery()
    {
        $db = $this->getConnection();

        // query
        $sql = 'SELECT * FROM {{customer}}';
        $reader = $db->createCommand($sql)->query();
        $this->assertInstanceOf(DataReader::className(), $reader);

        // queryAll
        $rows = $db->createCommand('SELECT * FROM {{customer}}')->queryAll();
        $this->assertCount(3, $rows);
        $row = $rows[2];
        $this->assertEquals(3, $row['id']);
        $this->assertEquals('user3', $row['name']);

        $rows = $db->createCommand('SELECT * FROM {{customer}} WHERE [[id]] = 10')->queryAll();
        $this->assertEquals([], $rows);

        // queryOne
        $sql = 'SELECT * FROM {{customer}} ORDER BY [[id]]';
        $row = $db->createCommand($sql)->queryOne();
        $this->assertEquals(1, $row['id']);
        $this->assertEquals('user1', $row['name']);

        $sql = 'SELECT * FROM {{customer}} ORDER BY [[id]]';
        $command = $db->createCommand($sql);
        $command->prepare();
        $row = $command->queryOne();
        $this->assertEquals(1, $row['id']);
        $this->assertEquals('user1', $row['name']);

        $sql = 'SELECT * FROM {{customer}} WHERE [[id]] = 10';
        $command = $db->createCommand($sql);
        $this->assertFalse($command->queryOne());

        // queryColumn
        $sql = 'SELECT * FROM {{customer}}';
        $column = $db->createCommand($sql)->queryColumn();
        $this->assertEquals(range(1, 3), $column);

        $command = $db->createCommand('SELECT [[id]] FROM {{customer}} WHERE [[id]] = 10');
        $this->assertEquals([], $command->queryColumn());

        // queryScalar
        $sql = 'SELECT * FROM {{customer}} ORDER BY [[id]]';
        $this->assertEquals($db->createCommand($sql)->queryScalar(), 1);

        $sql = 'SELECT [[id]] FROM {{customer}} ORDER BY [[id]]';
        $command = $db->createCommand($sql);
        $command->prepare();
        $this->assertEquals(1, $command->queryScalar());

        $command = $db->createCommand('SELECT [[id]] FROM {{customer}} WHERE [[id]] = 10');
        $this->assertFalse($command->queryScalar());

        $command = $db->createCommand('bad SQL');
        $this->expectException('\yii\db\Exception');
        $command->query();
    }

    public function testBindParamValue()
    {
        if (defined('HHVM_VERSION') && $this->driverName === 'pgsql') {
            $this->markTestSkipped('HHVMs PgSQL implementation has some specific behavior that breaks some parts of this test.');
        }

        $db = $this->getConnection();

        // bindParam
        $sql = 'INSERT INTO {{customer}}([[email]], [[name]], [[address]]) VALUES (:email, :name, :address)';
        $command = $db->createCommand($sql);
        $email = 'user4@example.com';
        $name = 'user4';
        $address = 'address4';
        $command->bindParam(':email', $email);
        $command->bindParam(':name', $name);
        $command->bindParam(':address', $address);
        $command->execute();

        $sql = 'SELECT [[name]] FROM {{customer}} WHERE [[email]] = :email';
        $command = $db->createCommand($sql);
        $command->bindParam(':email', $email);
        $this->assertEquals($name, $command->queryScalar());

        $sql = <<<'SQL'
INSERT INTO {{type}} ([[int_col]], [[char_col]], [[float_col]], [[blob_col]], [[numeric_col]], [[bool_col]])
  VALUES (:int_col, :char_col, :float_col, :blob_col, :numeric_col, :bool_col)
SQL;
        $command = $db->createCommand($sql);
        $intCol = 123;
        $charCol = str_repeat('abc', 33) . 'x'; // a 100 char string
        $boolCol = false;
        $command->bindParam(':int_col', $intCol, \PDO::PARAM_INT);
        $command->bindParam(':char_col', $charCol);
        $command->bindParam(':bool_col', $boolCol, \PDO::PARAM_BOOL);
        if ($this->driverName === 'oci') {
            // can't bind floats without support from a custom PDO driver
            $floatCol = 2;
            $numericCol = 3;
            // can't use blobs without support from a custom PDO driver
            $blobCol = null;
            $command->bindParam(':float_col', $floatCol, \PDO::PARAM_INT);
            $command->bindParam(':numeric_col', $numericCol, \PDO::PARAM_INT);
            $command->bindParam(':blob_col', $blobCol);
        } else {
            $floatCol = 1.23;
            $numericCol = '1.23';
            $blobCol = "\x10\x11\x12";
            $command->bindParam(':float_col', $floatCol);
            $command->bindParam(':numeric_col', $numericCol);
            $command->bindParam(':blob_col', $blobCol);
        }
        $this->assertEquals(1, $command->execute());

        $command = $db->createCommand('SELECT [[int_col]], [[char_col]], [[float_col]], [[blob_col]], [[numeric_col]], [[bool_col]] FROM {{type}}');
//        $command->prepare();
//        $command->pdoStatement->bindColumn('blob_col', $bc, \PDO::PARAM_LOB);
        $row = $command->queryOne();
        $this->assertEquals($intCol, $row['int_col']);
        $this->assertEquals($charCol, $row['char_col']);
        $this->assertEquals($floatCol, $row['float_col']);
        if ($this->driverName === 'mysql' || $this->driverName === 'sqlite' || $this->driverName === 'oci') {
            $this->assertEquals($blobCol, $row['blob_col']);
        } elseif (defined('HHVM_VERSION') && $this->driverName === 'pgsql') {
            // HHVMs pgsql implementation does not seem to support blob columns correctly.
        } else {
            $this->assertInternalType('resource', $row['blob_col']);
            $this->assertEquals($blobCol, stream_get_contents($row['blob_col']));
        }
        $this->assertEquals($numericCol, $row['numeric_col']);
        if ($this->driverName === 'mysql' || $this->driverName === 'oci' || (defined('HHVM_VERSION') && in_array($this->driverName, ['sqlite', 'pgsql']))) {
            $this->assertEquals($boolCol, (int) $row['bool_col']);
        } else {
            $this->assertEquals($boolCol, $row['bool_col']);
        }

        // bindValue
        $sql = 'INSERT INTO {{customer}}([[email]], [[name]], [[address]]) VALUES (:email, \'user5\', \'address5\')';
        $command = $db->createCommand($sql);
        $command->bindValue(':email', 'user5@example.com');
        $command->execute();

        $sql = 'SELECT [[email]] FROM {{customer}} WHERE [[name]] = :name';
        $command = $db->createCommand($sql);
        $command->bindValue(':name', 'user5');
        $this->assertEquals('user5@example.com', $command->queryScalar());
    }

    public function paramsNonWhereProvider()
    {
        return[
            ['SELECT SUBSTR(name, :len) FROM {{customer}} WHERE [[email]] = :email GROUP BY SUBSTR(name, :len)'],
            ['SELECT SUBSTR(name, :len) FROM {{customer}} WHERE [[email]] = :email ORDER BY SUBSTR(name, :len)'],
            ['SELECT SUBSTR(name, :len) FROM {{customer}} WHERE [[email]] = :email'],
        ];
    }

    /**
     * Test whether param binding works in other places than WHERE
     * @dataProvider paramsNonWhereProvider
     */
    public function testBindParamsNonWhere($sql)
    {
        $db = $this->getConnection();

        $db->createCommand()->insert('customer', ['name' => 'testParams', 'email' => 'testParams@example.com', 'address' => '1'])->execute();

        $params = [
            ':email' => 'testParams@example.com',
            ':len' => 5,
        ];
        $command = $db->createCommand($sql, $params);
        $this->assertEquals('Params', $command->queryScalar());
    }

    public function testFetchMode()
    {
        $db = $this->getConnection();

        // default: FETCH_ASSOC
        $sql = 'SELECT * FROM {{customer}}';
        $command = $db->createCommand($sql);
        $result = $command->queryOne();
        $this->assertTrue(is_array($result) && isset($result['id']));

        // FETCH_OBJ, customized via fetchMode property
        $sql = 'SELECT * FROM {{customer}}';
        $command = $db->createCommand($sql);
        $command->fetchMode = \PDO::FETCH_OBJ;
        $result = $command->queryOne();
        $this->assertInternalType('object', $result);

        // FETCH_NUM, customized in query method
        $sql = 'SELECT * FROM {{customer}}';
        $command = $db->createCommand($sql);
        $result = $command->queryOne([], \PDO::FETCH_NUM);
        $this->assertTrue(is_array($result) && isset($result[0]));
    }

    public function testBatchInsert()
    {
        $command = $this->getConnection()->createCommand();
        $command->batchInsert(
            '{{customer}}',
            ['email', 'name', 'address'],
            [
                ['t1@example.com', 't1', 't1 address'],
                ['t2@example.com', null, false],
            ]
        );
        $this->assertEquals(2, $command->execute());

        // @see https://github.com/yiisoft/yii2/issues/11693
        $command = $this->getConnection()->createCommand();
        $command->batchInsert(
            '{{customer}}',
            ['email', 'name', 'address'],
            []
        );
        $this->assertEquals(0, $command->execute());
    }

    public function testBatchInsertWithYield()
    {
        if (version_compare(PHP_VERSION, '5.5', '<')) {
            $this->markTestSkipped('The yield function is only supported with php 5.5 =< version');
        } else {
            include __DIR__ . '/testBatchInsertWithYield.php';
        }
    }

    public function testInsert()
    {
        $db = $this->getConnection();
        $db->createCommand('DELETE FROM {{customer}}')->execute();

        $command = $db->createCommand();
        $command->insert(
            '{{customer}}',
            [
                'email' => 't1@example.com',
                'name' => 'test',
                'address' => 'test address',
            ]
        )->execute();
        $this->assertEquals(1, $db->createCommand('SELECT COUNT(*) FROM {{customer}};')->queryScalar());
        $record = $db->createCommand('SELECT [[email]], [[name]], [[address]] FROM {{customer}}')->queryOne();
        $this->assertEquals([
            'email' => 't1@example.com',
            'name' => 'test',
            'address' => 'test address',
        ], $record);
    }

    /**
     * verify that {{}} are not going to be replaced in parameters
     */
    public function testNoTablenameReplacement()
    {
        $db = $this->getConnection();

        $db->createCommand()->insert(
            '{{customer}}',
            [
                'id' => 43,
                'name' => 'Some {{weird}} name',
                'email' => 'test@example.com',
                'address' => 'Some {{%weird}} address',
            ]
        )->execute();
        $customer = $db->createCommand('SELECT * FROM {{customer}} WHERE id=43')->queryOne();
        $this->assertEquals('Some {{weird}} name', $customer['name']);
        $this->assertEquals('Some {{%weird}} address', $customer['address']);

        $db->createCommand()->update(
            '{{customer}}',
            [
                'name' => 'Some {{updated}} name',
                'address' => 'Some {{%updated}} address',
            ],
            ['id' => 43]
        )->execute();
        $customer = $db->createCommand('SELECT * FROM {{customer}} WHERE id=43')->queryOne();
        $this->assertEquals('Some {{updated}} name', $customer['name']);
        $this->assertEquals('Some {{%updated}} address', $customer['address']);
    }

    /**
     * Test INSERT INTO ... SELECT SQL statement
     */
    public function testInsertSelect()
    {
        $db = $this->getConnection();
        $db->createCommand('DELETE FROM {{customer}}')->execute();

        $command = $db->createCommand();
        $command->insert(
            '{{customer}}',
            [
                'email' => 't1@example.com',
                'name' => 'test',
                'address' => 'test address',
            ]
        )->execute();

        $query = new \yii\db\Query();
        $query->select([
                    '{{customer}}.[[email]] as name',
                    '[[name]] as email',
                    '[[address]]',
                ]
        )
            ->from('{{customer}}')
            ->where([
                'and',
                ['<>', 'name', 'foo'],
                ['status' => [0, 1, 2, 3]],
            ]);

        $command = $db->createCommand();
        $command->insert(
            '{{customer}}',
            $query
        )->execute();

        $this->assertEquals(2, $db->createCommand('SELECT COUNT(*) FROM {{customer}}')->queryScalar());
        $record = $db->createCommand('SELECT [[email]], [[name]], [[address]] FROM {{customer}}')->queryAll();
        $this->assertEquals([
            [
                'email' => 't1@example.com',
                'name' => 'test',
                'address' => 'test address',
            ],
            [
                'email' => 'test',
                'name' => 't1@example.com',
                'address' => 'test address',
            ],
        ], $record);
    }

    /**
     * Test INSERT INTO ... SELECT SQL statement with alias syntax
     */
    public function testInsertSelectAlias()
    {
        $db = $this->getConnection();
        $db->createCommand('DELETE FROM {{customer}}')->execute();

        $command = $db->createCommand();
        $command->insert(
            '{{customer}}',
            [
                'email' => 't1@example.com',
                'name' => 'test',
                'address' => 'test address',
            ]
        )->execute();

        $query = new \yii\db\Query();
        $query->select([
                'email' => '{{customer}}.[[email]]',
                'address' => 'name',
                'name' => 'address',
            ]
        )
            ->from('{{customer}}')
            ->where([
                'and',
                ['<>', 'name', 'foo'],
                ['status' => [0, 1, 2, 3]],
            ]);

        $command = $db->createCommand();
        $command->insert(
            '{{customer}}',
            $query
        )->execute();

        $this->assertEquals(2, $db->createCommand('SELECT COUNT(*) FROM {{customer}}')->queryScalar());
        $record = $db->createCommand('SELECT [[email]], [[name]], [[address]] FROM {{customer}}')->queryAll();
        $this->assertEquals([
            [
                'email' => 't1@example.com',
                'name' => 'test',
                'address' => 'test address',
            ],
            [
                'email' => 't1@example.com',
                'name' => 'test address',
                'address' => 'test',
            ],
        ], $record);
    }

    /**
     * Data provider for testInsertSelectFailed
     * @return array
     */
    public function invalidSelectColumns()
    {
        return [
            [[]],
            ['*'],
            [['*']],
        ];
    }

    /**
     * Test INSERT INTO ... SELECT SQL statement with wrong query object
     *
     * @dataProvider invalidSelectColumns
     * @expectedException \yii\base\InvalidParamException
     * @expectedExceptionMessage Expected select query object with enumerated (named) parameters
     */
    public function testInsertSelectFailed($invalidSelectColumns)
    {
        $query = new \yii\db\Query();
        $query->select($invalidSelectColumns)->from('{{customer}}');

        $db = $this->getConnection();
        $command = $db->createCommand();
        $command->insert(
            '{{customer}}',
            $query
        )->execute();
    }

    public function testInsertExpression()
    {
        $db = $this->getConnection();
        $db->createCommand('DELETE FROM {{order_with_null_fk}}')->execute();

        switch ($this->driverName) {
            case 'pgsql':
                $expression = "EXTRACT(YEAR FROM TIMESTAMP 'now')";
            break;
            case 'cubrid':
            case 'mysql':
                $expression = 'YEAR(NOW())';
            break;
            case 'sqlite':
                $expression = "strftime('%Y')";
            break;
            case 'sqlsrv':
                $expression = 'YEAR(GETDATE())';
        }

        $command = $db->createCommand();
        $command->insert(
            '{{order_with_null_fk}}',
            [
                'created_at' => new Expression($expression),
                'total' => 1,
            ]
        )->execute();
        $this->assertEquals(1, $db->createCommand('SELECT COUNT(*) FROM {{order_with_null_fk}}')->queryScalar());
        $record = $db->createCommand('SELECT [[created_at]] FROM {{order_with_null_fk}}')->queryOne();
        $this->assertEquals([
            'created_at' => date('Y'),
        ], $record);
    }

    public function testsInsertQueryAsColumnValue()
    {
        $time = time();

        $db = $this->getConnection();
        $db->createCommand('DELETE FROM {{order_with_null_fk}}')->execute();

        $command = $db->createCommand();
        $command->insert('{{order}}', [
            'id' => 42,
            'customer_id' => 1,
            'created_at' => $time,
            'total' => 42,
        ])->execute();

        $columnValueQuery = new \yii\db\Query();
        $columnValueQuery->select('created_at')->from('{{order}}')->where(['id' => '42']);

        $command = $db->createCommand();
        $command->insert(
            '{{order_with_null_fk}}',
            [
                'customer_id' => 42,
                'created_at' => $columnValueQuery,
                'total' => 42,
            ]
        )->execute();

        $this->assertEquals($time, $db->createCommand('SELECT [[created_at]] FROM {{order_with_null_fk}} WHERE [[customer_id]] = 42')->queryScalar());

        $db->createCommand('DELETE FROM {{order_with_null_fk}}')->execute();
        $db->createCommand('DELETE FROM {{order}} WHERE [[id]] = 42')->execute();
    }

    public function testCreateTable()
    {
        $db = $this->getConnection();

        if ($db->getSchema()->getTableSchema('testCreateTable') !== null) {
            $db->createCommand()->dropTable('testCreateTable')->execute();
        }

        $db->createCommand()->createTable('testCreateTable', ['id' => Schema::TYPE_PK, 'bar' => Schema::TYPE_INTEGER])->execute();
        $db->createCommand()->insert('testCreateTable', ['bar' => 1])->execute();
        $records = $db->createCommand('SELECT [[id]], [[bar]] FROM {{testCreateTable}};')->queryAll();
        $this->assertEquals([
            ['id' => 1, 'bar' => 1],
        ], $records);
    }

    public function testAlterTable()
    {
        if ($this->driverName === 'sqlite') {
            $this->markTestSkipped('Sqlite does not support alterTable');
        }

        $db = $this->getConnection();

        if ($db->getSchema()->getTableSchema('testAlterTable') !== null) {
            $db->createCommand()->dropTable('testAlterTable')->execute();
        }

        $db->createCommand()->createTable('testAlterTable', ['id' => Schema::TYPE_PK, 'bar' => Schema::TYPE_INTEGER])->execute();
        $db->createCommand()->insert('testAlterTable', ['bar' => 1])->execute();

        $db->createCommand()->alterColumn('testAlterTable', 'bar', Schema::TYPE_STRING)->execute();

        $db->createCommand()->insert('testAlterTable', ['bar' => 'hello'])->execute();
        $records = $db->createCommand('SELECT [[id]], [[bar]] FROM {{testAlterTable}};')->queryAll();
        $this->assertEquals([
            ['id' => 1, 'bar' => 1],
            ['id' => 2, 'bar' => 'hello'],
        ], $records);
    }

    public function testDropTable()
    {
        $db = $this->getConnection();

        $tableName = 'type';
        $this->assertNotNull($db->getSchema()->getTableSchema($tableName));
        $db->createCommand()->dropTable($tableName)->execute();
        $this->assertNull($db->getSchema()->getTableSchema($tableName));
    }

    public function testTruncateTable()
    {
        $db = $this->getConnection();

        $rows = $db->createCommand('SELECT * FROM {{animal}}')->queryAll();
        $this->assertCount(2, $rows);
        $db->createCommand()->truncateTable('animal')->execute();
        $rows = $db->createCommand('SELECT * FROM {{animal}}')->queryAll();
        $this->assertCount(0, $rows);
    }

    public function testRenameTable()
    {
        $db = $this->getConnection();

        $fromTableName = 'type';
        $toTableName = 'new_type';

        if ($db->getSchema()->getTableSchema($toTableName) !== null) {
            $db->createCommand()->dropTable($toTableName)->execute();
        }

        $this->assertNotNull($db->getSchema()->getTableSchema($fromTableName));
        $this->assertNull($db->getSchema()->getTableSchema($toTableName));

        $db->createCommand()->renameTable($fromTableName, $toTableName)->execute();

        $this->assertNull($db->getSchema()->getTableSchema($fromTableName, true));
        $this->assertNotNull($db->getSchema()->getTableSchema($toTableName, true));
    }

    /*
    public function testUpdate()
    {
    }

    public function testDelete()
    {
    }

    public function testAddColumn()
    {
    }

    public function testDropColumn()
    {
    }

    public function testRenameColumn()
    {
    }
    */

    public function testAddDropPrimaryKey()
    {
        $db = $this->getConnection(false);
        $tableName = 'test_pk';
        $name = 'test_pk_constraint';
        /** @var \yii\db\pgsql\Schema $schema */
        $schema = $db->getSchema();

        if ($schema->getTableSchema($tableName) !== null) {
            $db->createCommand()->dropTable($tableName)->execute();
        }
        $db->createCommand()->createTable($tableName, [
            'int1' => 'integer not null',
            'int2' => 'integer not null',
        ])->execute();

        $this->assertNull($schema->getTablePrimaryKey($tableName, true));
        $db->createCommand()->addPrimaryKey($name, $tableName, ['int1'])->execute();
        $this->assertEquals(['int1'], $schema->getTablePrimaryKey($tableName, true)->columnNames);

        $db->createCommand()->dropPrimaryKey($name, $tableName)->execute();
        $this->assertNull($schema->getTablePrimaryKey($tableName, true));

        $db->createCommand()->addPrimaryKey($name, $tableName, ['int1', 'int2'])->execute();
        $this->assertEquals(['int1', 'int2'], $schema->getTablePrimaryKey($tableName, true)->columnNames);
    }

    public function testAddDropForeignKey()
    {
        $db = $this->getConnection(false);
        $tableName = 'test_fk';
        $name = 'test_fk_constraint';
        /** @var \yii\db\pgsql\Schema $schema */
        $schema = $db->getSchema();

        if ($schema->getTableSchema($tableName) !== null) {
            $db->createCommand()->dropTable($tableName)->execute();
        }
        $db->createCommand()->createTable($tableName, [
            'int1' => 'integer not null unique',
            'int2' => 'integer not null unique',
            'int3' => 'integer not null unique',
            'int4' => 'integer not null unique',
            'unique ([[int1]], [[int2]])',
            'unique ([[int3]], [[int4]])',
        ])->execute();

        $this->assertEmpty($schema->getTableForeignKeys($tableName, true));
        $db->createCommand()->addForeignKey($name, $tableName, ['int1'], $tableName, ['int3'])->execute();
        $this->assertEquals(['int1'], $schema->getTableForeignKeys($tableName, true)[0]->columnNames);
        $this->assertEquals(['int3'], $schema->getTableForeignKeys($tableName, true)[0]->foreignColumnNames);

        $db->createCommand()->dropForeignKey($name, $tableName)->execute();
        $this->assertEmpty($schema->getTableForeignKeys($tableName, true));

        $db->createCommand()->addForeignKey($name, $tableName, ['int1', 'int2'], $tableName, ['int3', 'int4'])->execute();
        $this->assertEquals(['int1', 'int2'], $schema->getTableForeignKeys($tableName, true)[0]->columnNames);
        $this->assertEquals(['int3', 'int4'], $schema->getTableForeignKeys($tableName, true)[0]->foreignColumnNames);
    }

    public function testCreateDropIndex()
    {
        $db = $this->getConnection(false);
        $tableName = 'test_idx';
        $name = 'test_idx_constraint';
        /** @var \yii\db\pgsql\Schema $schema */
        $schema = $db->getSchema();

        if ($schema->getTableSchema($tableName) !== null) {
            $db->createCommand()->dropTable($tableName)->execute();
        }
        $db->createCommand()->createTable($tableName, [
            'int1' => 'integer not null',
            'int2' => 'integer not null',
        ])->execute();

        $this->assertEmpty($schema->getTableIndexes($tableName, true));
        $db->createCommand()->createIndex($name, $tableName, ['int1'])->execute();
        $this->assertEquals(['int1'], $schema->getTableIndexes($tableName, true)[0]->columnNames);
        $this->assertFalse($schema->getTableIndexes($tableName, true)[0]->isUnique);

        $db->createCommand()->dropIndex($name, $tableName)->execute();
        $this->assertEmpty($schema->getTableIndexes($tableName, true));

        $db->createCommand()->createIndex($name, $tableName, ['int1', 'int2'])->execute();
        $this->assertEquals(['int1', 'int2'], $schema->getTableIndexes($tableName, true)[0]->columnNames);
        $this->assertFalse($schema->getTableIndexes($tableName, true)[0]->isUnique);

        $db->createCommand()->dropIndex($name, $tableName)->execute();
        $this->assertEmpty($schema->getTableIndexes($tableName, true));

        $this->assertEmpty($schema->getTableIndexes($tableName, true));
        $db->createCommand()->createIndex($name, $tableName, ['int1'], true)->execute();
        $this->assertEquals(['int1'], $schema->getTableIndexes($tableName, true)[0]->columnNames);
        $this->assertTrue($schema->getTableIndexes($tableName, true)[0]->isUnique);

        $db->createCommand()->dropIndex($name, $tableName)->execute();
        $this->assertEmpty($schema->getTableIndexes($tableName, true));

        $db->createCommand()->createIndex($name, $tableName, ['int1', 'int2'], true)->execute();
        $this->assertEquals(['int1', 'int2'], $schema->getTableIndexes($tableName, true)[0]->columnNames);
        $this->assertTrue($schema->getTableIndexes($tableName, true)[0]->isUnique);
    }

    public function testAddDropUnique()
    {
        $db = $this->getConnection(false);
        $tableName = 'test_uq';
        $name = 'test_uq_constraint';
        /** @var \yii\db\pgsql\Schema $schema */
        $schema = $db->getSchema();

        if ($schema->getTableSchema($tableName) !== null) {
            $db->createCommand()->dropTable($tableName)->execute();
        }
        $db->createCommand()->createTable($tableName, [
            'int1' => 'integer not null',
            'int2' => 'integer not null',
        ])->execute();

        $this->assertEmpty($schema->getTableUniques($tableName, true));
        $db->createCommand()->addUnique($name, $tableName, ['int1'])->execute();
        $this->assertEquals(['int1'], $schema->getTableUniques($tableName, true)[0]->columnNames);

        $db->createCommand()->dropUnique($name, $tableName)->execute();
        $this->assertEmpty($schema->getTableUniques($tableName, true));

        $db->createCommand()->addUnique($name, $tableName, ['int1', 'int2'])->execute();
        $this->assertEquals(['int1', 'int2'], $schema->getTableUniques($tableName, true)[0]->columnNames);
    }

    public function testAddDropCheck()
    {
        $db = $this->getConnection(false);
        $tableName = 'test_ck';
        $name = 'test_ck_constraint';
        /** @var \yii\db\pgsql\Schema $schema */
        $schema = $db->getSchema();

        if ($schema->getTableSchema($tableName) !== null) {
            $db->createCommand()->dropTable($tableName)->execute();
        }
        $db->createCommand()->createTable($tableName, [
            'int1' => 'integer',
        ])->execute();

        $this->assertEmpty($schema->getTableChecks($tableName, true));
        $db->createCommand()->addCheck($name, $tableName, '[[int1]] > 1')->execute();
        $this->assertRegExp('/^.*int1.*>.*1.*$/', $schema->getTableChecks($tableName, true)[0]->expression);

        $db->createCommand()->dropCheck($name, $tableName)->execute();
        $this->assertEmpty($schema->getTableChecks($tableName, true));
    }

    public function testAddDropDefaultValue()
    {
        $this->markTestSkipped($this->driverName . ' does not support adding/dropping default value constraints.');
    }

    public function testIntegrityViolation()
    {
        $this->expectException('\yii\db\IntegrityException');

        $db = $this->getConnection();

        $sql = 'INSERT INTO {{profile}}([[id]], [[description]]) VALUES (123, \'duplicate\')';
        $command = $db->createCommand($sql);
        $command->execute();
        $command->execute();
    }

    public function testLastInsertId()
    {
        $db = $this->getConnection();

        $sql = 'INSERT INTO {{profile}}([[description]]) VALUES (\'non duplicate\')';
        $command = $db->createCommand($sql);
        $command->execute();
        $this->assertEquals(3, $db->getSchema()->getLastInsertID());
    }

    public function testQueryCache()
    {
        $db = $this->getConnection();
        $db->enableQueryCache = true;
        $db->queryCache = new FileCache(['cachePath' => '@yiiunit/runtime/cache']);
        $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id');

        $this->assertEquals('user1', $command->bindValue(':id', 1)->queryScalar());
        $update = $db->createCommand('UPDATE {{customer}} SET [[name]] = :name WHERE [[id]] = :id');
        $update->bindValues([':id' => 1, ':name' => 'user11'])->execute();
        $this->assertEquals('user11', $command->bindValue(':id', 1)->queryScalar());

        $db->cache(function (Connection $db) use ($command, $update) {
            $this->assertEquals('user2', $command->bindValue(':id', 2)->queryScalar());
            $update->bindValues([':id' => 2, ':name' => 'user22'])->execute();
            $this->assertEquals('user2', $command->bindValue(':id', 2)->queryScalar());

            $db->noCache(function () use ($command) {
                $this->assertEquals('user22', $command->bindValue(':id', 2)->queryScalar());
            });

            $this->assertEquals('user2', $command->bindValue(':id', 2)->queryScalar());
        }, 10);

        $db->enableQueryCache = false;
        $db->cache(function ($db) use ($command, $update) {
            $this->assertEquals('user22', $command->bindValue(':id', 2)->queryScalar());
            $update->bindValues([':id' => 2, ':name' => 'user2'])->execute();
            $this->assertEquals('user2', $command->bindValue(':id', 2)->queryScalar());
        }, 10);

        $db->enableQueryCache = true;
        $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id')->cache();
        $this->assertEquals('user11', $command->bindValue(':id', 1)->queryScalar());
        $update->bindValues([':id' => 1, ':name' => 'user1'])->execute();
        $this->assertEquals('user11', $command->bindValue(':id', 1)->queryScalar());
        $this->assertEquals('user1', $command->noCache()->bindValue(':id', 1)->queryScalar());

        $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id');
        $db->cache(function (Connection $db) use ($command, $update) {
            $this->assertEquals('user11', $command->bindValue(':id', 1)->queryScalar());
            $this->assertEquals('user1', $command->noCache()->bindValue(':id', 1)->queryScalar());
        }, 10);
    }

    public function testColumnCase()
    {
        $db = $this->getConnection(false);
        $this->assertEquals(\PDO::CASE_NATURAL, $db->slavePdo->getAttribute(\PDO::ATTR_CASE));

        $sql = 'SELECT [[customer_id]], [[total]] FROM {{order}}';
        $rows = $db->createCommand($sql)->queryAll();
        $this->assertTrue(isset($rows[0]));
        $this->assertTrue(isset($rows[0]['customer_id']));
        $this->assertTrue(isset($rows[0]['total']));

        $db->slavePdo->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_LOWER);
        $rows = $db->createCommand($sql)->queryAll();
        $this->assertTrue(isset($rows[0]));
        $this->assertTrue(isset($rows[0]['customer_id']));
        $this->assertTrue(isset($rows[0]['total']));

        $db->slavePdo->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_UPPER);
        $rows = $db->createCommand($sql)->queryAll();
        $this->assertTrue(isset($rows[0]));
        $this->assertTrue(isset($rows[0]['CUSTOMER_ID']));
        $this->assertTrue(isset($rows[0]['TOTAL']));
    }

    /**
     * Data provider for [[testGetRawSql()]]
     * @return array test data
     */
    public function dataProviderGetRawSql()
    {
        return [
            [
                'SELECT * FROM customer WHERE id = :id',
                [':id' => 1],
                'SELECT * FROM customer WHERE id = 1',
            ],
            [
                'SELECT * FROM customer WHERE id = :id',
                ['id' => 1],
                'SELECT * FROM customer WHERE id = 1',
            ],
            [
                'SELECT * FROM customer WHERE id = :id',
                ['id' => null],
                'SELECT * FROM customer WHERE id = NULL',
            ],
            [
                'SELECT * FROM customer WHERE id = :base OR id = :basePrefix',
                [
                    'base' => 1,
                    'basePrefix' => 2,
                ],
                'SELECT * FROM customer WHERE id = 1 OR id = 2',
            ],
            // https://github.com/yiisoft/yii2/issues/9268
            [
                'SELECT * FROM customer WHERE active = :active',
                [':active' => false],
                'SELECT * FROM customer WHERE active = FALSE',
            ],
        ];
    }

    /**
     * @see https://github.com/yiisoft/yii2/issues/8592
     *
     * @dataProvider dataProviderGetRawSql
     *
     * @param string $sql
     * @param array $params
     * @param string $expectedRawSql
     */
    public function testGetRawSql($sql, array $params, $expectedRawSql)
    {
        $db = $this->getConnection(false);
        $command = $db->createCommand($sql, $params);
        $this->assertEquals($expectedRawSql, $command->getRawSql());
    }

    public function testAutoRefreshTableSchema()
    {
        $db = $this->getConnection(false);
        $tableName = 'test';
        $fkName = 'test_fk';

        if ($db->getSchema()->getTableSchema($tableName) !== null) {
            $db->createCommand()->dropTable($tableName)->execute();
        }

        $this->assertNull($db->getSchema()->getTableSchema($tableName));

        $db->createCommand()->createTable($tableName, [
            'id' => 'pk',
            'fk' => 'int',
            'name' => 'string',
        ])->execute();
        $initialSchema = $db->getSchema()->getTableSchema($tableName);
        $this->assertNotNull($initialSchema);

        $db->createCommand()->addColumn($tableName, 'value', 'integer')->execute();
        $newSchema = $db->getSchema()->getTableSchema($tableName);
        $this->assertNotEquals($initialSchema, $newSchema);

        if ($this->driverName !== 'sqlite') {
            $db->createCommand()->addForeignKey($fkName, $tableName, 'fk', $tableName, 'id')->execute();
            $this->assertNotEmpty($db->getSchema()->getTableSchema($tableName)->foreignKeys);

            $db->createCommand()->dropForeignKey($fkName, $tableName)->execute();
            $this->assertEmpty($db->getSchema()->getTableSchema($tableName)->foreignKeys);

            $db->createCommand()->addCommentOnColumn($tableName, 'id', 'Test comment')->execute();
            $this->assertNotEmpty($db->getSchema()->getTableSchema($tableName)->getColumn('id')->comment);

            $db->createCommand()->dropCommentFromColumn($tableName, 'id')->execute();
            $this->assertEmpty($db->getSchema()->getTableSchema($tableName)->getColumn('id')->comment);
        }

        $db->createCommand()->dropTable($tableName)->execute();
        $this->assertNull($db->getSchema()->getTableSchema($tableName));
    }
}
