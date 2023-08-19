<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\oci;

use yii\caching\ArrayCache;
use yii\db\Connection;
use yii\db\Query;
use yii\db\Schema;

/**
 * @group db
 * @group oci
 */
class CommandTest extends \yiiunit\framework\db\CommandTest
{
    protected $driverName = 'oci';

    public function testAutoQuoting()
    {
        $db = $this->getConnection(false);

        $sql = 'SELECT [[id]], [[t.name]] FROM {{customer}} t';
        $command = $db->createCommand($sql);
        $this->assertEquals('SELECT "id", "t"."name" FROM "customer" t', $command->sql);
    }

    public function testLastInsertId()
    {
        $db = $this->getConnection();

        $sql = 'INSERT INTO {{profile}}([[description]]) VALUES (\'non duplicate\')';
        $command = $db->createCommand($sql);
        $command->execute();
        $this->assertEquals(3, $db->getSchema()->getLastInsertID('profile_SEQ'));
    }

    public function batchInsertSqlProvider()
    {
        $data = parent::batchInsertSqlProvider();
        $data['issue11242']['expected'] = 'INSERT ALL  INTO "type" ("int_col", "float_col", "char_col") ' .
            "VALUES (NULL, NULL, 'Kyiv {{city}}, Ukraine') SELECT 1 FROM SYS.DUAL";
        $data['wrongBehavior']['expected'] = 'INSERT ALL  INTO "type" ("type"."int_col", "float_col", "char_col") ' .
            "VALUES ('', '', 'Kyiv {{city}}, Ukraine') SELECT 1 FROM SYS.DUAL";
        $data['batchInsert binds params from expression']['expected'] = 'INSERT ALL  INTO "type" ("int_col") ' .
            'VALUES (:qp1) SELECT 1 FROM SYS.DUAL';

        return $data;
    }

    /**
     * Testing the "ORA-01461: can bind a LONG value only for insert into a LONG column"
     *
     * @return void
     */
    public function testCLOBStringInsertion()
    {
        $db = $this->getConnection();

        if ($db->getSchema()->getTableSchema('longstring') !== null) {
            $db->createCommand()->dropTable('longstring')->execute();
        }

        $db->createCommand()->createTable('longstring', ['message' => Schema::TYPE_TEXT])->execute();

        $longData = str_pad('-', 4001, '-=', STR_PAD_LEFT);
        $db->createCommand()->insert('longstring', [
            'message' => $longData,
        ])->execute();

        $this->assertEquals(1, $db->createCommand('SELECT count(*) FROM {{longstring}}')->queryScalar());

        $db->createCommand()->dropTable('longstring')->execute();
    }

    public function testQueryCache()
    {
        $db = $this->getConnection(true);

        $db->enableQueryCache = true;
        $db->queryCache = new ArrayCache();

        $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id');

        $this->assertEquals('user1', $command->bindValue(':id', 1)->queryScalar());

        $update = $db->createCommand('UPDATE {{customer}} SET [[name]] = :name WHERE [[id]] = :id');
        $update->bindValues([':id' => 1, ':name' => 'user11'])->execute();

        $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id');

        $this->assertEquals('user11', $command->bindValue(':id', 1)->queryScalar());

        $db->cache(function (Connection $db) use ($update) {
            $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id');

            $this->assertEquals('user2', $command->bindValue(':id', 2)->queryScalar());

            $update->bindValues([':id' => 2, ':name' => 'user22'])->execute();

            $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id');

            $this->assertEquals('user2', $command->bindValue(':id', 2)->queryScalar());

            $db->noCache(function () use ($db) {
                $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id');

                $this->assertEquals('user22', $command->bindValue(':id', 2)->queryScalar());
            });

            $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id');

            $this->assertEquals('user2', $command->bindValue(':id', 2)->queryScalar());
        }, 10);

        $db->enableQueryCache =false;

        $db->cache(function (Connection $db) use ($update) {
            $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id');

            $this->assertEquals('user22', $command->bindValue(':id', 2)->queryScalar());

            $update->bindValues([':id' => 2, ':name' => 'user2'])->execute();

            $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id');

            $this->assertEquals('user2', $command->bindValue(':id', 2)->queryScalar());
        }, 10);

        $db->enableQueryCache = true;

        $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id')->cache();

        $this->assertEquals('user11', $command->bindValue(':id', 1)->queryScalar());

        $update->bindValues([':id' => 1, ':name' => 'user1'])->execute();

        $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id')->cache();

        $this->assertEquals('user11', $command->bindValue(':id', 1)->queryScalar());

        $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id')->noCache();

        $this->assertEquals('user1', $command->bindValue(':id', 1)->queryScalar());

        $db->cache(function (Connection $db) use ($update) {
            $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id');

            $this->assertEquals('user11', $command->bindValue(':id', 1)->queryScalar());

            $command = $db->createCommand('SELECT [[name]] FROM {{customer}} WHERE [[id]] = :id')->noCache();

            $this->assertEquals('user1', $command->bindValue(':id', 1)->queryScalar());
        }, 10);
    }

    public function paramsNonWhereProvider()
    {
        return [
            ['SELECT SUBSTR([[name]], :len) FROM {{customer}} WHERE [[email]] = :email GROUP BY SUBSTR([[name]], :len)'],
            ['SELECT SUBSTR([[name]], :len) FROM {{customer}} WHERE [[email]] = :email ORDER BY SUBSTR([[name]], :len)'],
            ['SELECT SUBSTR([[name]], :len) FROM {{customer}} WHERE [[email]] = :email'],
        ];
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
        $this->assertEquals(1, $db->createCommand('SELECT COUNT(*) FROM {{customer}}')->queryScalar());
        $record = $db->createCommand('SELECT [[email]], [[name]], [[address]] FROM {{customer}}')->queryOne();
        $this->assertEquals([
            'email' => 't1@example.com',
            'name' => 'test',
            'address' => 'test address',
        ], $record);
    }

    /**
     * Test INSERT INTO ... SELECT SQL statement with alias syntax.
     */
    public function testInsertSelectAlias()
    {
        $db = $this->getConnection();

        $db->createCommand('DELETE FROM {{customer}}')->execute();

        $command = $db->createCommand();

        $command->insert(
            '{{customer}}',
            [
                'email'   => 't1@example.com',
                'name'    => 'test',
                'address' => 'test address',
            ]
        )->execute();

        $query = $db->createCommand(
            "SELECT 't2@example.com' as [[email]], [[address]] as [[name]], [[name]] as [[address]] from {{customer}}"
        );

        $command->insert(
            '{{customer}}',
            $query->queryOne()
        )->execute();

        $this->assertEquals(2, $db->createCommand('SELECT COUNT(*) FROM {{customer}}')->queryScalar());

        $record = $db->createCommand('SELECT [[email]], [[name]], [[address]] FROM {{customer}}')->queryAll();

        $this->assertEquals([
            [
                'email'   => 't1@example.com',
                'name'    => 'test',
                'address' => 'test address',
            ],
            [
                'email'   => 't2@example.com',
                'name'    => 'test address',
                'address' => 'test',
            ],
        ], $record);
    }

    /**
     * Test batch insert with different data types.
     *
     * Ensure double is inserted with `.` decimal separator.
     *
     * https://github.com/yiisoft/yii2/issues/6526
     */
    public function testBatchInsertDataTypesLocale()
    {
        $locale = setlocale(LC_NUMERIC, 0);
        if (false === $locale) {
            $this->markTestSkipped('Your platform does not support locales.');
        }
        $db = $this->getConnection();

        try {
            // This one sets decimal mark to comma sign
            setlocale(LC_NUMERIC, 'ru_RU.UTF-8');

            $cols = ['int_col', 'char_col', 'float_col', 'bool_col'];
            $data = [
                [1, 'A', 9.735, '1'],
                [2, 'B', -2.123, '0'],
                [3, 'C', 2.123, '0'],
            ];

            // clear data in "type" table
            $db->createCommand()->delete('type')->execute();
            // batch insert on "type" table
            $db->createCommand()->batchInsert('type', $cols, $data)->execute();

            // change , for point oracle.
            $db->createCommand("ALTER SESSION SET NLS_NUMERIC_CHARACTERS='.,'")->execute();

            $data = $db->createCommand(
                'SELECT [[int_col]], [[char_col]], [[float_col]], [[bool_col]] FROM {{type}} WHERE [[int_col]] ' .
                'IN (1,2,3) ORDER BY [[int_col]]'
            )->queryAll();

            $this->assertEquals(3, \count($data));
            $this->assertEquals(1, $data[0]['int_col']);
            $this->assertEquals(2, $data[1]['int_col']);
            $this->assertEquals(3, $data[2]['int_col']);
            $this->assertEquals('A', rtrim($data[0]['char_col'])); // rtrim because Postgres padds the column with whitespace
            $this->assertEquals('B', rtrim($data[1]['char_col']));
            $this->assertEquals('C', rtrim($data[2]['char_col']));
            $this->assertEquals('9.735', $data[0]['float_col']);
            $this->assertEquals('-2.123', $data[1]['float_col']);
            $this->assertEquals('2.123', $data[2]['float_col']);
            $this->assertEquals('1', $data[0]['bool_col']);
            $this->assertIsOneOf($data[1]['bool_col'], ['0', false]);
            $this->assertIsOneOf($data[2]['bool_col'], ['0', false]);

        } catch (\Exception $e) {
            setlocale(LC_NUMERIC, $locale);
            throw $e;
        } catch (\Throwable $e) {
            setlocale(LC_NUMERIC, $locale);
            throw $e;
        }
        setlocale(LC_NUMERIC, $locale);
    }

    /**
     * verify that {{}} are not going to be replaced in parameters.
     */
    public function testNoTablenameReplacement()
    {
        $db = $this->getConnection();

        $db->createCommand()->insert(
            '{{customer}}',
            [
                'name' => 'Some {{weird}} name',
                'email' => 'test@example.com',
                'address' => 'Some {{%weird}} address',
            ]
        )->execute();

        $customerId = $db->getLastInsertID('customer_SEQ');

        $customer = $db->createCommand('SELECT * FROM {{customer}} WHERE [[id]]=' . $customerId)->queryOne();
        $this->assertEquals('Some {{weird}} name', $customer['name']);
        $this->assertEquals('Some {{%weird}} address', $customer['address']);

        $db->createCommand()->update(
            '{{customer}}',
            [
                'name' => 'Some {{updated}} name',
                'address' => 'Some {{%updated}} address',
            ],
            ['id' => $customerId]
        )->execute();
        $customer = $db->createCommand('SELECT * FROM {{customer}} WHERE [[id]]=' . $customerId)->queryOne();
        $this->assertEquals('Some {{updated}} name', $customer['name']);
        $this->assertEquals('Some {{%updated}} address', $customer['address']);
    }

    public function testCreateTable()
    {
        $db = $this->getConnection();

        if ($db->getSchema()->getTableSchema("testCreateTable") !== null) {
            $db->createCommand("DROP SEQUENCE testCreateTable_SEQ")->execute();
            $db->createCommand()->dropTable("testCreateTable")->execute();
        }

        $db->createCommand()->createTable(
            '{{testCreateTable}}',
            ['id' => Schema::TYPE_PK, 'bar' => Schema::TYPE_INTEGER]
        )->execute();

        $db->createCommand('CREATE SEQUENCE testCreateTable_SEQ START with 1 INCREMENT BY 1')->execute();

        $db->createCommand(
            'INSERT INTO {{testCreateTable}} ("id", "bar") VALUES(testCreateTable_SEQ.NEXTVAL, 1)'
        )->execute();

        $records = $db->createCommand('SELECT [[id]], [[bar]] FROM {{testCreateTable}}')->queryAll();

        $this->assertEquals([
            ['id' => 1, 'bar' => 1],
        ], $records);
    }

    public function testsInsertQueryAsColumnValue()
    {
        $time = time();

        $db = $this->getConnection();
        $db->createCommand('DELETE FROM {{order_with_null_fk}}')->execute();

        $command = $db->createCommand();
        $command->insert('{{order}}', [
            'customer_id' => 1,
            'created_at' => $time,
            'total' => 42,
        ])->execute();

        $orderId = $db->getLastInsertID('order_SEQ');

        $columnValueQuery = new \yii\db\Query();
        $columnValueQuery->select('created_at')->from('{{order}}')->where(['id' => $orderId]);

        $command = $db->createCommand();
        $command->insert(
            '{{order_with_null_fk}}',
            [
                'customer_id' => $orderId,
                'created_at' => $columnValueQuery,
                'total' => 42,
            ]
        )->execute();

        $this->assertEquals($time, $db->createCommand(
            'SELECT [[created_at]] FROM {{order_with_null_fk}} WHERE [[customer_id]] = ' . $orderId
        )->queryScalar());

        $db->createCommand('DELETE FROM {{order_with_null_fk}}')->execute();
        $db->createCommand('DELETE FROM {{order}} WHERE [[id]] = ' . $orderId)->execute();
    }

    public function testAlterTable()
    {
        $db = $this->getConnection();

        if ($db->getSchema()->getTableSchema('testAlterTable') !== null) {
            $db->createCommand("DROP SEQUENCE testAlterTable_SEQ")->execute();
            $db->createCommand()->dropTable('testAlterTable')->execute();
        }

        $db->createCommand()->createTable(
            'testAlterTable',
            ['id' => Schema::TYPE_PK, 'bar' => Schema::TYPE_INTEGER]
        )->execute();

        $db->createCommand('CREATE SEQUENCE testAlterTable_SEQ START with 1 INCREMENT BY 1')->execute();

        $db->createCommand(
            'INSERT INTO {{testAlterTable}} ([[id]], [[bar]]) VALUES(testAlterTable_SEQ.NEXTVAL, 1)'
        )->execute();

        $db->createCommand('ALTER TABLE {{testAlterTable}} ADD ([[bar_tmp]] VARCHAR(20))')->execute();

        $db->createCommand('UPDATE {{testAlterTable}} SET [[bar_tmp]] = [[bar]]')->execute();

        $db->createCommand('ALTER TABLE {{testAlterTable}} DROP COLUMN [[bar]]')->execute();

        $db->createCommand('ALTER TABLE {{testAlterTable}} RENAME COLUMN [[bar_tmp]] TO [[bar]]')->execute();

        $db->createCommand(
            "INSERT INTO {{testAlterTable}} ([[id]], [[bar]]) VALUES(testAlterTable_SEQ.NEXTVAL, 'hello')"
        )->execute();

        $records = $db->createCommand('SELECT [[id]], [[bar]] FROM {{testAlterTable}}')->queryAll();

        $this->assertEquals([
            ['id' => 1, 'bar' => 1],
            ['id' => 2, 'bar' => 'hello'],
        ], $records);
    }

    public function testCreateView()
    {
        $db = $this->getConnection();

        $subquery = (new Query())
            ->select('bar')
            ->from('testCreateViewTable')
            ->where(['>', 'bar', '5']);

        if ($db->getSchema()->getTableSchema('testCreateView') !== null) {
            $db->createCommand()->dropView('testCreateView')->execute();
        }

        if ($db->getSchema()->getTableSchema('testCreateViewTable')) {
            $db->createCommand("DROP SEQUENCE testCreateViewTable_SEQ")->execute();
            $db->createCommand()->dropTable('testCreateViewTable')->execute();
        }

        $db->createCommand()->createTable('testCreateViewTable', [
            'id'  => Schema::TYPE_PK,
            'bar' => Schema::TYPE_INTEGER,
        ])->execute();

        $db->createCommand('CREATE SEQUENCE testCreateViewTable_SEQ START with 1 INCREMENT BY 1')->execute();

        $db->createCommand(
            'INSERT INTO {{testCreateViewTable}} ("id", "bar") VALUES(testCreateTable_SEQ.NEXTVAL, 1)'
        )->execute();

        $db->createCommand(
            'INSERT INTO {{testCreateViewTable}} ("id", "bar") VALUES(testCreateTable_SEQ.NEXTVAL, 6)'
        )->execute();

        $db->createCommand()->createView('testCreateView', $subquery)->execute();

        $records = $db->createCommand('SELECT [[bar]] FROM {{testCreateView}}')->queryAll();

        $this->assertEquals([['bar' => 6]], $records);
    }

    public function testColumnCase()
    {
        $this->markTestSkipped('Should be fixed.');
    }
}
