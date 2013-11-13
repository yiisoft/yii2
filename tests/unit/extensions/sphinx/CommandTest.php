<?php

namespace yiiunit\extensions\sphinx;

use yii\sphinx\DataReader;
use yii\db\Expression;

/**
 * @group sphinx
 */
class CommandTest extends SphinxTestCase
{
	protected function tearDown()
	{
		$this->truncateRuntimeIndex('yii2_test_rt_index');
		parent::tearDown();
	}

	// Tests :

	public function testConstruct()
	{
		$db = $this->getConnection(false);

		// null
		$command = $db->createCommand();
		$this->assertEquals(null, $command->sql);

		// string
		$sql = 'SELECT * FROM yii2_test_item_index';
		$params = [
			'name' => 'value'
		];
		$command = $db->createCommand($sql, $params);
		$this->assertEquals($sql, $command->sql);
		$this->assertEquals($params, $command->params);
	}

	public function testGetSetSql()
	{
		$db = $this->getConnection(false);

		$sql = 'SELECT * FROM yii2_test_item_index';
		$command = $db->createCommand($sql);
		$this->assertEquals($sql, $command->sql);

		$sql2 = 'SELECT * FROM yii2_test_item_index';
		$command->sql = $sql2;
		$this->assertEquals($sql2, $command->sql);
	}

	public function testAutoQuoting()
	{
		$db = $this->getConnection(false);

		$sql = 'SELECT [[id]], [[t.name]] FROM {{yii2_test_item_index}} t';
		$command = $db->createCommand($sql);
		$this->assertEquals("SELECT `id`, `t`.`name` FROM `yii2_test_item_index` t", $command->sql);
	}

	public function testPrepareCancel()
	{
		$db = $this->getConnection(false);

		$command = $db->createCommand('SELECT * FROM yii2_test_item_index');
		$this->assertEquals(null, $command->pdoStatement);
		$command->prepare();
		$this->assertNotEquals(null, $command->pdoStatement);
		$command->cancel();
		$this->assertEquals(null, $command->pdoStatement);
	}

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

	/**
	 * @depends testQuery
	 */
	public function testInsert()
	{
		$db = $this->getConnection();

		$command = $db->createCommand()->insert('yii2_test_rt_index', [
			'title' => 'Test title',
			'content' => 'Test content',
			'type_id' => 2,
			'category' => [1, 2],
			'id' => 1,
		]);
		$this->assertEquals(1, $command->execute(), 'Unable to execute insert!');

		$rows = $db->createCommand('SELECT * FROM yii2_test_rt_index')->queryAll();
		$this->assertEquals(1, count($rows), 'No row inserted!');
	}

	/**
	 * @depends testInsert
	 */
	public function testBatchInsert()
	{
		$db = $this->getConnection();

		$command = $db->createCommand()->batchInsert(
			'yii2_test_rt_index',
			[
				'title',
				'content',
				'type_id',
				'category',
				'id',
			],
			[
				[
					'Test title 1',
					'Test content 1',
					1,
					[1, 2],
					1,
				],
				[
					'Test title 2',
					'Test content 2',
					2,
					[3, 4],
					2,
				],
			]
		);
		$this->assertEquals(2, $command->execute(), 'Unable to execute batch insert!');

		$rows = $db->createCommand('SELECT * FROM yii2_test_rt_index')->queryAll();
		$this->assertEquals(2, count($rows), 'No rows inserted!');
	}

	/**
	 * @depends testInsert
	 */
	public function testUpdate()
	{
		$db = $this->getConnection();

		$db->createCommand()->insert('yii2_test_rt_index', [
			'title' => 'Test title',
			'content' => 'Test content',
			'type_id' => 2,
			'id' => 1,
		])->execute();

		$newTypeId = 5;
		$command = $db->createCommand()->update(
			'yii2_test_rt_index',
			[
				'type_id' => $newTypeId,
				'category' => [3, 4],
			],
			'id = 1'
		);
		$this->assertEquals(1, $command->execute(), 'Unable to execute update!');

		list($row) = $db->createCommand('SELECT * FROM yii2_test_rt_index')->queryAll();
		$this->assertEquals($newTypeId, $row['type_id'], 'Unable to update attribute value!');
	}

	/**
	 * @depends testInsert
	 */
	public function testDelete()
	{
		$db = $this->getConnection();

		$db->createCommand()->insert('yii2_test_rt_index', [
			'title' => 'Test title',
			'content' => 'Test content',
			'type_id' => 2,
			'id' => 1,
		])->execute();

		$command = $db->createCommand()->delete('yii2_test_rt_index', 'id = 1');
		$this->assertEquals(1, $command->execute(), 'Unable to execute delete!');

		$rows = $db->createCommand('SELECT * FROM yii2_test_rt_index')->queryAll();
		$this->assertEquals(0, count($rows), 'Unable to delete record!');
	}

	public function testCallSnippets()
	{
		$db = $this->getConnection();

		$query = 'pencil';
		$data = ['Some data sentence about ' . $query];
		$rows = $db->createCommand()->callSnippets('yii2_test_item_index', $data, $query)->queryColumn();
		$this->assertNotEmpty($rows, 'Unable to call snippets!');
		$this->assertContains('<b>' . $query . '</b>', $rows[0], 'Query not present in the snippet!');
	}

	public function testCallKeywords()
	{
		$db = $this->getConnection();

		$text = 'table pencil';
		$rows = $db->createCommand()->callKeywords('yii2_test_item_index', $text)->queryAll();
		$this->assertNotEmpty($rows, 'Unable to call keywords!');
		$this->assertArrayHasKey('tokenized', $rows[0], 'No tokenized keyword!');
		$this->assertArrayHasKey('normalized', $rows[0], 'No normalized keyword!');

		$text = 'table pencil';
		$rows = $db->createCommand()->callKeywords('yii2_test_item_index', $text, true)->queryAll();
		$this->assertNotEmpty($rows, 'Unable to call keywords with statistic!');
		$this->assertArrayHasKey('docs', $rows[0], 'No docs!');
		$this->assertArrayHasKey('hits', $rows[0], 'No hits!');
	}
}