<?php

namespace yiiunit\framework\db;

use yii\db\Connection;
use yii\db\Command;
use yii\db\Query;
use yii\db\DataReader;

class CommandTest extends \yiiunit\DatabaseTestCase
{
	function testConstruct()
	{
		$db = $this->getConnection(false);

		// null
		$command = $db->createCommand();
		$this->assertEquals(null, $command->sql);

		// string
		$sql = 'SELECT * FROM tbl_customer';
		$command = $db->createCommand($sql);
		$this->assertEquals($sql, $command->sql);
	}

	function testGetSetSql()
	{
		$db = $this->getConnection(false);

		$sql = 'SELECT * FROM tbl_customer';
		$command = $db->createCommand($sql);
		$this->assertEquals($sql, $command->sql);

		$sql2 = 'SELECT * FROM tbl_order';
		$command->sql = $sql2;
		$this->assertEquals($sql2, $command->sql);
	}

	function testAutoQuoting()
	{
		$db = $this->getConnection(false);

		$sql = 'SELECT [[id]], [[t.name]] FROM {{tbl_customer}} t';
		$command = $db->createCommand($sql);
		$this->assertEquals("SELECT `id`, `t`.`name` FROM `tbl_customer` t", $command->sql);
	}

	function testPrepareCancel()
	{
		$db = $this->getConnection(false);

		$command = $db->createCommand('SELECT * FROM tbl_customer');
		$this->assertEquals(null, $command->pdoStatement);
		$command->prepare();
		$this->assertNotEquals(null, $command->pdoStatement);
		$command->cancel();
		$this->assertEquals(null, $command->pdoStatement);
	}

	function testExecute()
	{
		$db = $this->getConnection();

		$sql = 'INSERT INTO tbl_customer(email, name , address) VALUES (\'user4@example.com\', \'user4\', \'address4\')';
		$command = $db->createCommand($sql);
		$this->assertEquals(1, $command->execute());

		$sql = 'SELECT COUNT(*) FROM tbl_customer WHERE name =\'user4\'';
		$command = $db->createCommand($sql);
		$this->assertEquals(1, $command->queryScalar());

		$command = $db->createCommand('bad SQL');
		$this->setExpectedException('\yii\db\Exception');
		$command->execute();
	}

	function testQuery()
	{
		$db = $this->getConnection();

		// query
		$sql = 'SELECT * FROM tbl_customer';
		$reader = $db->createCommand($sql)->query();
		$this->assertTrue($reader instanceof DataReader);

		// queryAll
		$rows = $db->createCommand('SELECT * FROM tbl_customer')->queryAll();
		$this->assertEquals(3, count($rows));
		$row = $rows[2];
		$this->assertEquals(3, $row['id']);
		$this->assertEquals('user3', $row['name']);

		$rows = $db->createCommand('SELECT * FROM tbl_customer WHERE id=10')->queryAll();
		$this->assertEquals(array(), $rows);

		// queryRow
		$sql = 'SELECT * FROM tbl_customer ORDER BY id';
		$row = $db->createCommand($sql)->queryRow();
		$this->assertEquals(1, $row['id']);
		$this->assertEquals('user1', $row['name']);

		$sql = 'SELECT * FROM tbl_customer ORDER BY id';
		$command = $db->createCommand($sql);
		$command->prepare();
		$row = $command->queryRow();
		$this->assertEquals(1, $row['id']);
		$this->assertEquals('user1', $row['name']);

		$sql = 'SELECT * FROM tbl_customer WHERE id=10';
		$command = $db->createCommand($sql);
		$this->assertFalse($command->queryRow());

		// queryColumn
		$sql = 'SELECT * FROM tbl_customer';
		$column = $db->createCommand($sql)->queryColumn();
		$this->assertEquals(range(1, 3), $column);

		$command = $db->createCommand('SELECT id FROM tbl_customer WHERE id=10');
		$this->assertEquals(array(), $command->queryColumn());

		// queryScalar
		$sql = 'SELECT * FROM tbl_customer ORDER BY id';
		$this->assertEquals($db->createCommand($sql)->queryScalar(), 1);

		$sql = 'SELECT id FROM tbl_customer ORDER BY id';
		$command = $db->createCommand($sql);
		$command->prepare();
		$this->assertEquals(1, $command->queryScalar());

		$command = $db->createCommand('SELECT id FROM tbl_customer WHERE id=10');
		$this->assertFalse($command->queryScalar());

		$command = $db->createCommand('bad SQL');
		$this->setExpectedException('\yii\db\Exception');
		$command->query();
	}

	function testBindParamValue()
	{
		$db = $this->getConnection();

		// bindParam
		$sql = 'INSERT INTO tbl_customer(email, name, address) VALUES (:email, :name, :address)';
		$command = $db->createCommand($sql);
		$email = 'user4@example.com';
		$name = 'user4';
		$address = 'address4';
		$command->bindParam(':email', $email);
		$command->bindParam(':name', $name);
		$command->bindParam(':address', $address);
		$command->execute();

		$sql = 'SELECT name FROM tbl_customer WHERE email=:email';
		$command = $db->createCommand($sql);
		$command->bindParam(':email', $email);
		$this->assertEquals($name, $command->queryScalar());

		$sql = 'INSERT INTO tbl_type (int_col, char_col, float_col, blob_col, numeric_col, bool_col) VALUES (:int_col, :char_col, :float_col, :blob_col, :numeric_col, :bool_col)';
		$command = $db->createCommand($sql);
		$intCol = 123;
		$charCol = 'abc';
		$floatCol = 1.23;
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

		$sql = 'SELECT * FROM tbl_type';
		$row = $db->createCommand($sql)->queryRow();
		$this->assertEquals($intCol, $row['int_col']);
		$this->assertEquals($charCol, $row['char_col']);
		$this->assertEquals($floatCol, $row['float_col']);
		$this->assertEquals($blobCol, $row['blob_col']);
		$this->assertEquals($numericCol, $row['numeric_col']);

		// bindValue
		$sql = 'INSERT INTO tbl_customer(email, name, address) VALUES (:email, \'user5\', \'address5\')';
		$command = $db->createCommand($sql);
		$command->bindValue(':email', 'user5@example.com');
		$command->execute();

		$sql = 'SELECT email FROM tbl_customer WHERE name=:name';
		$command = $db->createCommand($sql);
		$command->bindValue(':name', 'user5');
		$this->assertEquals('user5@example.com', $command->queryScalar());
	}

	function testFetchMode()
	{
		$db = $this->getConnection();

		// default: FETCH_ASSOC
		$sql = 'SELECT * FROM tbl_customer';
		$command = $db->createCommand($sql);
		$result = $command->queryRow();
		$this->assertTrue(is_array($result) && isset($result['id']));

		// FETCH_OBJ, customized via fetchMode property
		$sql = 'SELECT * FROM tbl_customer';
		$command = $db->createCommand($sql);
		$command->fetchMode = \PDO::FETCH_OBJ;
		$result = $command->queryRow();
		$this->assertTrue(is_object($result));

		// FETCH_NUM, customized in query method
		$sql = 'SELECT * FROM tbl_customer';
		$command = $db->createCommand($sql);
		$result = $command->queryRow(array(), \PDO::FETCH_NUM);
		$this->assertTrue(is_array($result) && isset($result[0]));
	}

	function testInsert()
	{

	}

	function testUpdate()
	{

	}

	function testDelete()
	{

	}

	function testCreateTable()
	{

	}

	function testRenameTable()
	{

	}

	function testDropTable()
	{

	}

	function testTruncateTable()
	{

	}

	function testAddColumn()
	{

	}

	function testDropColumn()
	{

	}

	function testRenameColumn()
	{

	}

	function testAlterColumn()
	{

	}

	function testAddForeignKey()
	{

	}

	function testDropForeignKey()
	{

	}

	function testCreateIndex()
	{

	}

	function testDropIndex()
	{

	}
}
