<?php

namespace yiiunit\framework\db\dao;

use yii\db\dao\Connection;
use yii\db\dao\Command;
use yii\db\dao\Query;
use yii\db\dao\DataReader;

class CommandTest extends \yiiunit\MysqlTestCase
{
	function testConstruct()
	{
		$db = $this->getConnection(false);

		$command = $db->createCommand();
		$this->assertEquals("SELECT *\nFROM ", $command->sql);

		$sql = 'SELECT * FROM yii_post';
		$command = $db->createCommand($sql);
		$this->assertEquals($sql, $command->sql);

		$query = new Query;
		$command = $db->createCommand($query);
		$this->assertEquals($query, $command->query);

		$query = array('select' => 'id', 'from' => 'yii_post');
		$command = $db->createCommand($query);
		$this->assertEquals($query, $command->query->toArray());
	}

	function testReset()
	{
		$db = $this->getConnection();

		$command = $db->createCommand('SELECT * FROM yii_user');
		$command->queryRow();
		$this->assertNotEquals(null, $command->pdoStatement);
		$this->assertEquals('SELECT * FROM yii_user', $command->sql);

		$command->reset();
		$this->assertEquals(null, $command->pdoStatement);
		$this->assertNotEquals('SELECT * FROM yii_user', $command->sql);
	}

	function testGetSetSql()
	{
		$db = $this->getConnection(false);

		$sql = 'SELECT * FROM yii_user';
		$command = $db->createCommand($sql);
		$this->assertEquals($sql, $command->sql);

		$sql2 = 'SELECT * FROM yii_yii_post';
		$command->sql = $sql2;
		$this->assertEquals($sql2, $command->sql);
	}

	function testPrepareCancel()
	{
		$db = $this->getConnection(false);

		$command = $db->createCommand('SELECT * FROM yii_user');
		$this->assertEquals(null, $command->pdoStatement);
		$command->prepare();
		$this->assertNotEquals(null, $command->pdoStatement);
		$command->cancel();
		$this->assertEquals(null, $command->pdoStatement);
	}

	function testExecute()
	{
		$db = $this->getConnection();

		$sql = 'INSERT INTO yii_comment(content,post_id,author_id) VALUES (\'test comment\', 1, 1)';
		$command = $db->createCommand($sql);
		$this->assertEquals(1, $command->execute());

		$sql = 'SELECT COUNT(*) FROM yii_comment WHERE content=\'test comment\'';
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
		$sql = 'SELECT * FROM yii_post';
		$reader = $db->createCommand($sql)->query();
		$this->assertTrue($reader instanceof DataReader);

		// queryAll
		$rows = $db->createCommand('SELECT * FROM yii_post')->queryAll();
		$this->assertEquals(5, count($rows));
		$row = $rows[2];
		$this->assertEquals(3, $row['id']);
		$this->assertEquals($row['title'], 'post 3');

		$rows = $db->createCommand('SELECT * FROM yii_post WHERE id=10')->queryAll();
		$this->assertEquals(array(), $rows);

		// queryRow
		$sql = 'SELECT * FROM yii_post';
		$row = $db->createCommand($sql)->queryRow();
		$this->assertEquals(1, $row['id']);
		$this->assertEquals('post 1', $row['title'], 'post 1');

		$sql = 'SELECT * FROM yii_post';
		$command = $db->createCommand($sql);
		$command->prepare();
		$row = $command->queryRow();
		$this->assertEquals(1, $row['id']);
		$this->assertEquals('post 1', $row['title']);

		$sql = 'SELECT * FROM yii_post WHERE id=10';
		$command = $db->createCommand($sql);
		$this->assertFalse($command->queryRow());

		// queryColumn
		$sql = 'SELECT * FROM yii_post';
		$column = $db->createCommand($sql)->queryColumn();
		$this->assertEquals(range(1, 5), $column);

		$command = $db->createCommand('SELECT id FROM yii_post WHERE id=10');
		$this->assertEquals(array(), $command->queryColumn());

		// queryScalar
		$sql = 'SELECT * FROM yii_post';
		$this->assertEquals($db->createCommand($sql)->queryScalar(), 1);

		$sql = 'SELECT id FROM yii_post';
		$command = $db->createCommand($sql);
		$command->prepare();
		$this->assertEquals(1, $command->queryScalar());

		$command = $db->createCommand('SELECT id FROM yii_post WHERE id=10');
		$this->assertFalse($command->queryScalar());

		$command = $db->createCommand('bad SQL');
		$this->setExpectedException('\yii\db\Exception');
		$command->query();
	}

	function testBindParamValue()
	{
		$db = $this->getConnection();

		// bindParam
		$sql = 'INSERT INTO yii_post(title,create_time,author_id) VALUES (:title, :create_time, 1)';
		$command = $db->createCommand($sql);
		$title = 'test title';
		$createTime = time();
		$command->bindParam(':title', $title);
		$command->bindParam(':create_time', $createTime);
		$command->execute();

		$sql = 'SELECT create_time FROM yii_post WHERE title=:title';
		$command = $db->createCommand($sql);
		$command->bindParam(':title', $title);
		$this->assertEquals($createTime, $command->queryScalar());

		$sql = 'INSERT INTO yii_type (int_col, char_col, float_col, blob_col, numeric_col, bool_col) VALUES (:int_col, :char_col, :float_col, :blob_col, :numeric_col, :bool_col)';
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

		$sql = 'SELECT * FROM yii_type';
		$row = $db->createCommand($sql)->queryRow();
		$this->assertEquals($intCol, $row['int_col']);
		$this->assertEquals($charCol, $row['char_col']);
		$this->assertEquals($floatCol, $row['float_col']);
		$this->assertEquals($blobCol, $row['blob_col']);
		$this->assertEquals($numericCol, $row['numeric_col']);

		// bindValue
		$sql = 'INSERT INTO yii_comment(content,post_id,author_id) VALUES (:content, 1, 1)';
		$command = $db->createCommand($sql);
		$command->bindValue(':content', 'test comment');
		$command->execute();

		$sql = 'SELECT post_id FROM yii_comment WHERE content=:content';
		$command = $db->createCommand($sql);
		$command->bindValue(':content', 'test comment');
		$this->assertEquals(1, $command->queryScalar());

		// bind value via query or execute method
		$sql = 'INSERT INTO yii_comment(content,post_id,author_id) VALUES (:content, 1, 1)';
		$command = $db->createCommand($sql);
		$command->execute(array(':content' => 'test comment2'));
		$sql = 'SELECT post_id FROM yii_comment WHERE content=:content';
		$command = $db->createCommand($sql);
		$this->assertEquals(1, $command->queryScalar(array(':content' => 'test comment2')));
	}

	function testFetchMode()
	{
		$db = $this->getConnection();

		// default: FETCH_ASSOC
		$sql = 'SELECT * FROM yii_post';
		$command = $db->createCommand($sql);
		$result = $command->queryRow();
		$this->assertTrue(is_array($result) && isset($result['id']));

		// FETCH_OBJ, customized via fetchMode property
		$sql = 'SELECT * FROM yii_post';
		$command = $db->createCommand($sql);
		$command->fetchMode = \PDO::FETCH_OBJ;
		$result = $command->queryRow();
		$this->assertTrue(is_object($result));

		// FETCH_NUM, customized in query method
		$sql = 'SELECT * FROM yii_post';
		$command = $db->createCommand($sql);
		$result = $command->queryRow(array(), \PDO::FETCH_NUM);
		$this->assertTrue(is_array($result) && isset($result[0]));
	}
}