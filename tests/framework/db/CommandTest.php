<?php

namespace yiiunit\framework\db;

use yii\caching\FileCache;
use yii\db\Connection;
use yii\db\DataReader;
use yii\db\Expression;
use yii\db\Schema;

/**
 * @group db
 * @group mysql
 */
class CommandTest extends DatabaseTestCase
{
    public function testConstruct()
    {
        $db = $this->getConnection(false);

        // null
        $command = $db->createCommand();
        $this->assertEquals(null, $command->sql);

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
        $this->assertEquals("SELECT `id`, `t`.`name` FROM `customer` t", $command->sql);
    }

    public function testPrepareCancel()
    {
        $db = $this->getConnection(false);

        $command = $db->createCommand('SELECT * FROM {{customer}}');
        $this->assertEquals(null, $command->pdoStatement);
        $command->prepare();
        $this->assertNotEquals(null, $command->pdoStatement);
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
        $this->setExpectedException('\yii\db\Exception');
        $command->execute();
    }

    public function testQuery()
    {
        $db = $this->getConnection();

        // query
        $sql = 'SELECT * FROM {{customer}}';
        $reader = $db->createCommand($sql)->query();
        $this->assertTrue($reader instanceof DataReader);

        // queryAll
        $rows = $db->createCommand('SELECT * FROM {{customer}}')->queryAll();
        $this->assertEquals(3, count($rows));
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
        $this->setExpectedException('\yii\db\Exception');
        $command->query();
    }

    public function testBindParamValue()
    {
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

        $sql = <<<SQL
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
        } else {
            $this->assertTrue(is_resource($row['blob_col']));
            $this->assertEquals($blobCol, stream_get_contents($row['blob_col']));
        }
        $this->assertEquals($numericCol, $row['numeric_col']);
        if ($this->driverName === 'mysql' || (defined('HHVM_VERSION') && $this->driverName === 'sqlite') || $this->driverName === 'oci') {
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
        $this->assertTrue(is_object($result));

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
    }

    public function testInsert()
    {
        $db = $this->getConnection();
        $db->createCommand('DELETE FROM {{customer}};')->execute();

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
        $record = $db->createCommand('SELECT email, name, address FROM {{customer}};')->queryOne();
        $this->assertEquals([
            'email' => 't1@example.com',
            'name' => 'test',
            'address' => 'test address',
        ], $record);
    }

    public function testInsertExpression()
    {
        $db = $this->getConnection();
        $db->createCommand('DELETE FROM {{order_with_null_fk}};')->execute();

        switch($this->driverName){
            case 'pgsql': $expression = "EXTRACT(YEAR FROM TIMESTAMP 'now')"; break;
            case 'cubrid':
            case 'mysql': $expression = "YEAR(NOW())"; break;
            default:
            case 'sqlite': $expression = "strftime('%Y')"; break;
        }

        $command = $db->createCommand();
        $command->insert(
            '{{order_with_null_fk}}',
            [
                'created_at' => new Expression($expression),
                'total' => 1,
            ]
        )->execute();
        $this->assertEquals(1, $db->createCommand('SELECT COUNT(*) FROM {{order_with_null_fk}};')->queryScalar());
        $record = $db->createCommand('SELECT created_at FROM {{order_with_null_fk}};')->queryOne();
        $this->assertEquals([
            'created_at' => date('Y'),
        ], $record);
    }

    public function testCreateTable()
    {
        $db = $this->getConnection();
        $db->createCommand("DROP TABLE IF EXISTS testCreateTable;")->execute();

        $db->createCommand()->createTable('testCreateTable', ['id' => Schema::TYPE_PK, 'bar' => Schema::TYPE_INTEGER])->execute();
        $db->createCommand()->insert('testCreateTable', ['bar' => 1])->execute();
        $records = $db->createCommand('SELECT [[id]], [[bar]] FROM {{testCreateTable}};')->queryAll();
        $this->assertEquals([
            ['id' => 1, 'bar' => 1],
        ], $records);
    }

    public function testAlterTable()
    {
        if ($this->driverName === 'sqlite'){
            $this->markTestSkipped('Sqlite does not support alterTable');
        }

        $db = $this->getConnection();
        $db->createCommand("DROP TABLE IF EXISTS testAlterTable;")->execute();

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


    /*
    public function testUpdate()
    {
    }

    public function testDelete()
    {
    }

    public function testRenameTable()
    {
    }

    public function testDropTable()
    {
    }

    public function testTruncateTable()
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

    public function testAddForeignKey()
    {
    }

    public function testDropForeignKey()
    {
    }

    public function testCreateIndex()
    {
    }

    public function testDropIndex()
    {
    }
    */

    public function testIntegrityViolation()
    {
        $this->setExpectedException('\yii\db\IntegrityException');

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

        $db->createCommand()->createTable($tableName, [
            'id' => 'pk',
            'name' => 'string',
        ])->execute();
        $initialSchema = $db->getSchema()->getTableSchema($tableName);

        $db->createCommand()->addColumn($tableName, 'value', 'integer')->execute();
        $newSchema = $db->getSchema()->getTableSchema($tableName);
        $this->assertNotEquals($initialSchema, $newSchema);

        $db->createCommand()->dropTable($tableName)->execute();
        $this->assertNull($db->getSchema()->getTableSchema($tableName));
    }
}
