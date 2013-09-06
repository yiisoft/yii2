<?php

namespace yiiunit\framework\db\mssql;

use yiiunit\framework\db\CommandTest;

class MssqlCommandTest extends CommandTest
{
	protected $driverName = 'sqlsrv';

	public function testAutoQuoting()
	{
		$db = $this->getConnection(false);

		$sql = 'SELECT [[id]], [[t.name]] FROM {{tbl_customer}} t';
		$command = $db->createCommand($sql);
		$this->assertEquals("SELECT [id], [t].[name] FROM [tbl_customer] t", $command->sql);
	}

	public function testPrepareCancel()
	{
		$this->markTestSkipped('MSSQL driver does not support this feature.');
	}

	public function testBindParamValue()
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

		$sql = 'INSERT INTO tbl_type (int_col, char_col, float_col, blob_col, numeric_col, bool_col) VALUES (:int_col, :char_col, :float_col, CONVERT([varbinary], :blob_col), :numeric_col, :bool_col)';
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

		$sql = 'SELECT int_col, char_col, float_col, CONVERT([nvarchar], blob_col) AS blob_col, numeric_col FROM tbl_type';
		$row = $db->createCommand($sql)->queryOne();
		$this->assertEquals($intCol, $row['int_col']);
		$this->assertEquals($charCol, trim($row['char_col']));
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
}
