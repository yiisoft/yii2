<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\framework\db\mssql;

use yii\db\pgsql\Schema;
use yii\db\Query;

/**
 * @group db
 * @group mssql
 */
class CommandTest extends \yiiunit\framework\db\CommandTest
{
    protected $driverName = 'sqlsrv';

    public function testAutoQuoting(): void
    {
        $db = $this->getConnection(false);

        $sql = 'SELECT [[id]], [[t.name]] FROM {{customer}} t';
        $command = $db->createCommand($sql);
        $this->assertEquals('SELECT [id], [t].[name] FROM [customer] t', $command->sql);
    }

    public function testPrepareCancel(): void
    {
        $this->markTestSkipped('MSSQL driver does not support this feature.');
    }

    public function testBindParamValue(): void
    {
        $db = $this->getConnection();

        // bindParam
        $sql = 'INSERT INTO customer(email, name, address) VALUES (:email, :name, :address)';
        $command = $db->createCommand($sql);
        $email = 'user4@example.com';
        $name = 'user4';
        $address = 'address4';
        $command->bindParam(':email', $email);
        $command->bindParam(':name', $name);
        $command->bindParam(':address', $address);
        $command->execute();

        $sql = 'SELECT name FROM customer WHERE email=:email';
        $command = $db->createCommand($sql);
        $command->bindParam(':email', $email);
        $this->assertEquals($name, $command->queryScalar());

        $sql = 'INSERT INTO type (int_col, char_col, float_col, blob_col, numeric_col, bool_col) VALUES (:int_col, :char_col, :float_col, CONVERT([varbinary], :blob_col), :numeric_col, :bool_col)';
        $command = $db->createCommand($sql);
        $intCol = 123;
        $charCol = 'abc';
        $floatCol = 1.230;
        $blobCol = "\x10\x11\x12";
        $numericCol = '1.23';
        $boolCol = false;
        $command->bindParam(':int_col', $intCol);
        $command->bindParam(':char_col', $charCol);
        $command->bindParam(':float_col', $floatCol);
        $command->bindParam(':blob_col', $blobCol);
        $command->bindParam(':numeric_col', $numericCol);
        $command->bindParam(':bool_col', $boolCol);
        $this->assertEquals(1, $command->execute());

        $sql = 'SELECT int_col, char_col, float_col, CONVERT([nvarchar], blob_col) AS blob_col, numeric_col FROM type';
        $row = $db->createCommand($sql)->queryOne();

        $this->assertEquals($intCol, $row['int_col']);
        $this->assertEquals($charCol, trim($row['char_col']));
        $this->assertEquals($floatCol, (float) $row['float_col']);
        $this->assertEquals($blobCol, $row['blob_col']);
        $this->assertEquals($numericCol, $row['numeric_col']);

        // bindValue
        $sql = 'INSERT INTO customer(email, name, address) VALUES (:email, \'user5\', \'address5\')';
        $command = $db->createCommand($sql);
        $command->bindValue(':email', 'user5@example.com');
        $command->execute();

        $sql = 'SELECT email FROM customer WHERE name=:name';
        $command = $db->createCommand($sql);
        $command->bindValue(':name', 'user5');
        $this->assertEquals('user5@example.com', $command->queryScalar());
    }

    public static function paramsNonWhereProvider(): array
    {
        return[
            ['SELECT SUBSTRING(name, :len, 6) AS name FROM {{customer}} WHERE [[email]] = :email GROUP BY name'],
            ['SELECT SUBSTRING(name, :len, 6) as name FROM {{customer}} WHERE [[email]] = :email ORDER BY name'],
            ['SELECT SUBSTRING(name, :len, 6) FROM {{customer}} WHERE [[email]] = :email'],
        ];
    }

    public function testAddDropDefaultValue(): void
    {
        $db = $this->getConnection(false);
        $tableName = 'test_def';
        $name = 'test_def_constraint';
        /** @var Schema $schema */
        $schema = $db->getSchema();

        if ($schema->getTableSchema($tableName) !== null) {
            $db->createCommand()->dropTable($tableName)->execute();
        }
        $db->createCommand()->createTable($tableName, [
            'int1' => 'integer',
        ])->execute();

        $this->assertEmpty($schema->getTableDefaultValues($tableName, true));
        $db->createCommand()->addDefaultValue($name, $tableName, 'int1', 41)->execute();
        $this->assertMatchesRegularExpression('/^.*41.*$/', $schema->getTableDefaultValues($tableName, true)[0]->value);

        $db->createCommand()->dropDefaultValue($name, $tableName)->execute();
        $this->assertEmpty($schema->getTableDefaultValues($tableName, true));
    }

    public static function batchInsertSqlProvider(): array
    {
        $data = parent::batchInsertSqlProvider();
        $data['issue11242']['expected'] = 'INSERT INTO [type] ([int_col], [float_col], [char_col]) VALUES (NULL, NULL, \'Kyiv {{city}}, Ukraine\')';
        $data['wrongBehavior']['expected'] = 'INSERT INTO [type] ([type].[int_col], [float_col], [char_col]) VALUES (\'\', \'\', \'Kyiv {{city}}, Ukraine\')';
        $data['batchInsert binds params from expression']['expected'] = 'INSERT INTO [type] ([int_col]) VALUES (:qp1)';
        unset($data['batchIsert empty rows represented by ArrayObject']);

        return $data;
    }

    public function testBatchUpdate(): void
    {
        $db = $this->getConnection();
        $db->createCommand()->delete('customer')->execute();
        $emails = ['u1@example.com', 'u2@example.com', 'u3@example.com', 'u4@example.com'];
        $db->createCommand()->batchInsert('customer', ['email', 'name', 'address', 'status'], [
            ['u1@example.com', 'u1', 'a1', 0],
            ['u2@example.com', 'u2', 'a2', 0],
            ['u3@example.com', 'u3', 'a3', 0],
            ['u4@example.com', 'u4', 'a4', 0],
        ])->execute();

        $insertedRows = (new Query())
            ->select(['id', 'email'])
            ->from('customer')
            ->where(['email' => $emails])
            ->all($db);
        $ids = [];
        foreach ($insertedRows as $row) {
            $ids[$row['email']] = $row['id'];
        }
        $this->assertCount(4, $ids);

        $db->createCommand()->batchUpdate('customer', [
            ['id' => $ids['u1@example.com'], 'name' => 'updated-1', 'status' => 1],
            ['id' => $ids['u2@example.com'], 'address' => 'updated-a2'],
            ['id' => $ids['u3@example.com']],
            ['id' => $ids['u4@example.com'], 'name' => 'updated-u4'],
        ], 'id')->execute();

        $rows = (new Query())
            ->select(['id', 'email', 'name', 'address', 'status'])
            ->from('customer')
            ->where(['email' => $emails])
            ->orderBy(['email' => SORT_ASC])
            ->all($db);

        $this->assertEquals([
            ['id' => $ids['u1@example.com'], 'email' => 'u1@example.com', 'name' => 'updated-1', 'address' => 'a1', 'status' => 1],
            ['id' => $ids['u2@example.com'], 'email' => 'u2@example.com', 'name' => 'u2', 'address' => 'updated-a2', 'status' => 0],
            ['id' => $ids['u3@example.com'], 'email' => 'u3@example.com', 'name' => 'u3', 'address' => 'a3', 'status' => 0],
            ['id' => $ids['u4@example.com'], 'email' => 'u4@example.com', 'name' => 'updated-u4', 'address' => 'a4', 'status' => 0],
        ], $rows);
    }

    public static function batchUpdateSqlProvider(): array
    {
        $data = parent::batchUpdateSqlProvider();
        $data['sparse rows']['expectedParams'][':qp1'] = '2.5';

        return $data;
    }

    public function testUpsertVarbinary(): void
    {
        $db = $this->getConnection();

        $qb = $db->getQueryBuilder();
        $testData = json_encode(['test' => 'string', 'test2' => 'integer'], JSON_THROW_ON_ERROR);

        $params = [];

        $sql = $qb->upsert('T_upsert_varbinary', ['id' => 1, 'blob_col' => $testData], ['blob_col' => $testData], $params);
        $result = $db->createCommand($sql, $params)->execute();

        $this->assertSame(1, $result);

        $query = (new Query())->select(['blob_col'])->from('T_upsert_varbinary')->where(['id' => 1]);
        $resultData = $query->createCommand($db)->queryOne();

        $this->assertSame($testData, $resultData['blob_col']);
    }
}
