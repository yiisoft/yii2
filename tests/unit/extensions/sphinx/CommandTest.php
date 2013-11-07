<?php

namespace yiiunit\extensions\sphinx;

use yii\db\DataReader;

/**
 * @group sphinx
 */
class CommandTest extends SphinxTestCase
{
	public function testExecute()
	{
		$db = $this->getConnection();

		$sql = 'SELECT COUNT(*) FROM yii2_test_item_index WHERE MATCH(\'wooden\')';
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
		$sql = 'SELECT * FROM yii2_test_item_index';
		$reader = $db->createCommand($sql)->query();
		$this->assertTrue($reader instanceof DataReader);

		// queryAll
		$rows = $db->createCommand('SELECT * FROM yii2_test_item_index')->queryAll();
		$this->assertEquals(2, count($rows));
		$row = $rows[1];
		$this->assertEquals(2, $row['id']);
		$this->assertEquals(2, $row['category_id']);

		$rows = $db->createCommand('SELECT * FROM yii2_test_item_index WHERE id=10')->queryAll();
		$this->assertEquals([], $rows);

		// queryOne
		$sql = 'SELECT * FROM yii2_test_item_index ORDER BY id ASC';
		$row = $db->createCommand($sql)->queryOne();
		$this->assertEquals(1, $row['id']);
		$this->assertEquals(1, $row['category_id']);

		$sql = 'SELECT * FROM yii2_test_item_index ORDER BY id ASC';
		$command = $db->createCommand($sql);
		$command->prepare();
		$row = $command->queryOne();
		$this->assertEquals(1, $row['id']);
		$this->assertEquals(1, $row['category_id']);

		$sql = 'SELECT * FROM yii2_test_item_index WHERE id=10';
		$command = $db->createCommand($sql);
		$this->assertFalse($command->queryOne());

		// queryColumn
		$sql = 'SELECT * FROM yii2_test_item_index';
		$column = $db->createCommand($sql)->queryColumn();
		$this->assertEquals(range(1, 2), $column);

		$command = $db->createCommand('SELECT id FROM yii2_test_item_index WHERE id=10');
		$this->assertEquals([], $command->queryColumn());

		// queryScalar
		$sql = 'SELECT * FROM yii2_test_item_index ORDER BY id ASC';
		$this->assertEquals($db->createCommand($sql)->queryScalar(), 1);

		$sql = 'SELECT id FROM yii2_test_item_index ORDER BY id ASC';
		$command = $db->createCommand($sql);
		$command->prepare();
		$this->assertEquals(1, $command->queryScalar());

		$command = $db->createCommand('SELECT id FROM yii2_test_item_index WHERE id=10');
		$this->assertFalse($command->queryScalar());

		$command = $db->createCommand('bad SQL');
		$this->setExpectedException('\yii\db\Exception');
		$command->query();
	}
}