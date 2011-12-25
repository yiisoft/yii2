<?php

namespace yiiunit\framework\db\dao;

use yii\db\dao\Connection;
use yii\db\dao\Command;
use yii\db\dao\Query;

class CommandTest extends \yiiunit\MysqlTestCase
{
	function testConstruct()
	{
		$db = $this->getConnection(false);

		$command = $db->createCommand();
		$this->assertEquals("SELECT *\nFROM ", $command->sql);

		$sql='SELECT * FROM posts';
		$command = $db->createCommand($sql);
		$this->assertEquals($sql, $command->sql);

		$query = new Query;
		$command = $db->createCommand($query);
		$this->assertEquals($query, $command->query);

		$query = array('select'=>'id', 'from'=>'posts');
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

	}

	function testPrepare()
	{

	}

	function testBindParam()
	{

	}

	function testBindValue()
	{

	}

	function testExecute()
	{

	}

	function testQuery()
	{

	}

	function testQueryRow()
	{

	}

	function testQueryAll()
	{

	}

	function testQueryColumn()
	{

	}

	function testQueryScalar()
	{

	}

	function testFetchMode()
	{

	}

	/*
	function testPrepare()
	{
		$sql='SELECT title FROM posts';
		$command=$db->createCommand($sql);
		$this->assertEquals($command->pdoStatement,null);
		$command->prepare();
		$this->assertTrue($command->pdoStatement instanceof PDOStatement);
		$this->assertEquals($command->queryScalar(),'post 1');

		$command->text='Bad SQL';
		$this->setExpectedException('CException');
		$command->prepare();
	}

	function testCancel()
	{
		$sql='SELECT title FROM posts';
		$command=$db->createCommand($sql);
		$command->prepare();
		$this->assertTrue($command->pdoStatement instanceof PDOStatement);
		$command->cancel();
		$this->assertEquals($command->pdoStatement,null);
	}

	function testExecute()
	{
		$sql='INSERT INTO comments(content,post_id,author_id) VALUES (\'test comment\', 1, 1)';
		$command=$db->createCommand($sql);
		$this->assertEquals($command->execute(),1);
		$this->assertEquals($command->execute(),1);
		$command=$db->createCommand('SELECT * FROM comments WHERE content=\'test comment\'');
		$this->assertEquals($command->execute(),0);
		$command=$db->createCommand('SELECT COUNT(*) FROM comments WHERE content=\'test comment\'');
		$this->assertEquals($command->queryScalar(),2);

		$command=$db->createCommand('bad SQL');
		$this->setExpectedException('CException');
		$command->execute();
	}

	function testQuery()
	{
		$sql='SELECT * FROM posts';
		$reader=$db->createCommand($sql)->query();
		$this->assertTrue($reader instanceof CDbDataReader);

		$sql='SELECT * FROM posts';
		$command=$db->createCommand($sql);
		$command->prepare();
		$reader=$command->query();
		$this->assertTrue($reader instanceof CDbDataReader);

		$command=$db->createCommand('bad SQL');
		$this->setExpectedException('CException');
		$command->query();
	}

	function testBindParam()
	{
		$sql='INSERT INTO posts(title,create_time,author_id) VALUES (:title, :create_time, 1)';
		$command=$db->createCommand($sql);
		$title='test title';
		$createTime=time();
		$command->bindParam(':title',$title);
		$command->bindParam(':create_time',$createTime);
		$command->execute();

		$sql='SELECT create_time FROM posts WHERE title=:title';
		$command=$db->createCommand($sql);
		$command->bindParam(':title',$title);
		$this->assertEquals($command->queryScalar(),$createTime);

		$sql='INSERT INTO types (int_col, char_col, float_col, blob_col, numeric_col, bool_col) VALUES (:int_col, :char_col, :float_col, :blob_col, :numeric_col, :bool_col)';
		$command=$db->createCommand($sql);
		$intCol=123;
		$charCol='abc';
		$floatCol=1.23;
		$blobCol="\x10\x11\x12";
		$numericCol='1.23';
		$boolCol=false;
		$command->bindParam(':int_col',$intCol);
		$command->bindParam(':char_col',$charCol);
		$command->bindParam(':float_col',$floatCol);
		$command->bindParam(':blob_col',$blobCol);
		$command->bindParam(':numeric_col',$numericCol);
		$command->bindParam(':bool_col',$boolCol);
		$this->assertEquals(1,$command->execute());

		$sql='SELECT * FROM types';
		$row=$db->createCommand($sql)->queryRow();
		$this->assertEquals($row['int_col'],$intCol);
		$this->assertEquals($row['char_col'],$charCol);
		$this->assertEquals($row['float_col'],$floatCol);
		$this->assertEquals($row['blob_col'],$blobCol);
		$this->assertEquals($row['numeric_col'],$numericCol);
	}

	function testBindValue()
	{
		$sql='INSERT INTO comments(content,post_id,author_id) VALUES (:content, 1, 1)';
		$command=$db->createCommand($sql);
		$command->bindValue(':content','test comment');
		$command->execute();

		$sql='SELECT post_id FROM comments WHERE content=:content';
		$command=$db->createCommand($sql);
		$command->bindValue(':content','test comment');
		$this->assertEquals($command->queryScalar(),1);
	}

	function testQueryAll()
	{
		$rows=$db->createCommand('SELECT * FROM posts')->queryAll();
		$this->assertEquals(count($rows),5);
		$row=$rows[2];
		$this->assertEquals($row['id'],3);
		$this->assertEquals($row['title'],'post 3');

		$rows=$db->createCommand('SELECT * FROM posts WHERE id=10')->queryAll();
		$this->assertEquals($rows,array());
	}

	function testQueryRow()
	{
		$sql='SELECT * FROM posts';
		$row=$db->createCommand($sql)->queryRow();
		$this->assertEquals($row['id'],1);
		$this->assertEquals($row['title'],'post 1');

		$sql='SELECT * FROM posts';
		$command=$db->createCommand($sql);
		$command->prepare();
		$row=$command->queryRow();
		$this->assertEquals($row['id'],1);
		$this->assertEquals($row['title'],'post 1');

		$sql='SELECT * FROM posts WHERE id=10';
		$command=$db->createCommand($sql);
		$this->assertFalse($command->queryRow());

		$command=$db->createCommand('bad SQL');
		$this->setExpectedException('CException');
		$command->queryRow();
	}

	function testQueryColumn()
	{
		$sql='SELECT * FROM posts';
		$column=$db->createCommand($sql)->queryColumn();
		$this->assertEquals($column,range(1,5));

		$command=$db->createCommand('SELECT id FROM posts WHERE id=10');
		$this->assertEquals($command->queryColumn(),array());

		$command=$db->createCommand('bad SQL');
		$this->setExpectedException('CException');
		$command->queryColumn();
	}

	function testQueryScalar()
	{
		$sql='SELECT * FROM posts';
		$this->assertEquals($db->createCommand($sql)->queryScalar(),1);

		$sql='SELECT id FROM posts';
		$command=$db->createCommand($sql);
		$command->prepare();
		$this->assertEquals($command->queryScalar(),1);

		$command=$db->createCommand('SELECT id FROM posts WHERE id=10');
		$this->assertFalse($command->queryScalar());

		$command=$db->createCommand('bad SQL');
		$this->setExpectedException('CException');
		$command->queryScalar();
	}

	function testFetchMode(){
		$sql='SELECT * FROM posts';
		$command=$db->createCommand($sql);
		$result = $command->queryRow();
		$this->assertTrue(is_array($result));

		$sql='SELECT * FROM posts';
		$command=$db->createCommand($sql);
		$command->setFetchMode(PDO::FETCH_OBJ);
		$result = $command->queryRow();
		$this->assertTrue(is_object($result));
	}
	*/
}