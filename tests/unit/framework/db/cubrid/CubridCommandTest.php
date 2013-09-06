<?php
namespace yiiunit\framework\db\cubrid;

use yiiunit\framework\db\CommandTest;

class CubridCommandTest extends CommandTest
{
	protected function setUp()
	{
		$this->driverName = 'cubrid';
		parent::setUp();
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

		$sql = "INSERT INTO tbl_type (int_col, char_col, char_col2, enum_col, float_col, blob_col, numeric_col, bool_col, bool_col2) VALUES (:int_col, '', :char_col, :enum_col, :float_col, CHAR_TO_BLOB(:blob_col), :numeric_col, :bool_col, :bool_col2)";
		$command = $db->createCommand($sql);
		$intCol = 123;
		$charCol = 'abc';
		$enumCol = 'a';
		$floatCol = 1.23;
		$blobCol = "\x10\x11\x12";
		$numericCol = '1.23';
		$boolCol = false;
		$boolCol2 = true;
		$command->bindParam(':int_col', $intCol);
		$command->bindParam(':char_col', $charCol);
		$command->bindParam(':enum_col', $enumCol);
		$command->bindParam(':float_col', $floatCol);
		$command->bindParam(':blob_col', $blobCol);
		$command->bindParam(':numeric_col', $numericCol);
		$command->bindParam(':bool_col', $boolCol);
		$command->bindParam(':bool_col2', $boolCol2);
		$this->assertEquals(1, $command->execute());

		$sql = 'SELECT * FROM tbl_type';
		$row = $db->createCommand($sql)->queryOne();
		$this->assertEquals($intCol, $row['int_col']);
		$this->assertEquals($enumCol, $row['enum_col']);
		$this->assertEquals($charCol, $row['char_col2']);
		$this->assertEquals($floatCol, $row['float_col']);
		$this->assertEquals($blobCol, fread($row['blob_col'], 3));
		$this->assertEquals($numericCol, $row['numeric_col']);
		$this->assertEquals($boolCol, $row['bool_col']);
		$this->assertEquals($boolCol2, $row['bool_col2']);

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
