<?php

namespace yiiunit\framework\elasticsearch;

use yii\elasticsearch\Query;

/**
 * @group db
 * @group mysql
 */
class QueryTest extends ElasticSearchTestCase
{
	protected function setUp()
	{
		parent::setUp();

		$command = $this->getConnection()->createCommand();

		$command->deleteAllIndexes();

		$command->insert('test', 'user', ['name' => 'user1', 'email' => 'user1@example.com', 'status' => 1], 1);
		$command->insert('test', 'user', ['name' => 'user2', 'email' => 'user2@example.com', 'status' => 1], 2);
		$command->insert('test', 'user', ['name' => 'user3', 'email' => 'user3@example.com', 'status' => 2], 3);
		$command->insert('test', 'user', ['name' => 'user4', 'email' => 'user4@example.com', 'status' => 1], 4);

		$command->flushIndex();
	}

	public function testFields()
	{
		$query = new Query;
		$query->from('test', 'user');

		$query->fields(['name', 'status']);
		$this->assertEquals(['name', 'status'], $query->fields);

		$result = $query->one($this->getConnection());
		$this->assertEquals(2, count($result['_source']));
		$this->assertArrayHasKey('status', $result['_source']);
		$this->assertArrayHasKey('name', $result['_source']);
		$this->assertArrayHasKey('_id', $result);

		$query->fields([]);
		$this->assertEquals([], $query->fields);

		$result = $query->one($this->getConnection());
		$this->assertEquals([], $result['_source']);
		$this->assertArrayHasKey('_id', $result);

		$query->fields(null);
		$this->assertNull($query->fields);

		$result = $query->one($this->getConnection());
		$this->assertEquals(3, count($result['_source']));
		$this->assertArrayHasKey('status', $result['_source']);
		$this->assertArrayHasKey('email', $result['_source']);
		$this->assertArrayHasKey('name', $result['_source']);
		$this->assertArrayHasKey('_id', $result);
	}

	public function testOne()
	{
		$query = new Query;
		$query->from('test', 'user');

		$result = $query->one($this->getConnection());
		$this->assertEquals(3, count($result['_source']));
		$this->assertArrayHasKey('status', $result['_source']);
		$this->assertArrayHasKey('email', $result['_source']);
		$this->assertArrayHasKey('name', $result['_source']);
		$this->assertArrayHasKey('_id', $result);

		$result = $query->where(['name' => 'user1'])->one($this->getConnection());
		$this->assertEquals(3, count($result['_source']));
		$this->assertArrayHasKey('status', $result['_source']);
		$this->assertArrayHasKey('email', $result['_source']);
		$this->assertArrayHasKey('name', $result['_source']);
		$this->assertArrayHasKey('_id', $result);
		$this->assertEquals(1, $result['_id']);

		$result = $query->where(['name' => 'user5'])->one($this->getConnection());
		$this->assertFalse($result);
	}

	public function testAll()
	{
		$query = new Query;
		$query->from('test', 'user');

		$results = $query->all($this->getConnection());
		$this->assertEquals(4, count($results));
		$result = reset($results);
		$this->assertEquals(3, count($result['_source']));
		$this->assertArrayHasKey('status', $result['_source']);
		$this->assertArrayHasKey('email', $result['_source']);
		$this->assertArrayHasKey('name', $result['_source']);
		$this->assertArrayHasKey('_id', $result);

		$query = new Query;
		$query->from('test', 'user');

		$results = $query->where(['name' => 'user1'])->all($this->getConnection());
		$this->assertEquals(1, count($results));
		$result = reset($results);
		$this->assertEquals(3, count($result['_source']));
		$this->assertArrayHasKey('status', $result['_source']);
		$this->assertArrayHasKey('email', $result['_source']);
		$this->assertArrayHasKey('name', $result['_source']);
		$this->assertArrayHasKey('_id', $result);
		$this->assertEquals(1, $result['_id']);

		// indexBy
		$query = new Query;
		$query->from('test', 'user');

		$results = $query->indexBy('name')->all($this->getConnection());
		$this->assertEquals(4, count($results));
		ksort($results);
		$this->assertEquals(['user1', 'user2', 'user3', 'user4'], array_keys($results));
	}

	public function testScalar()
	{
		$query = new Query;
		$query->from('test', 'user');

		$result = $query->where(['name' => 'user1'])->scalar('name', $this->getConnection());
		$this->assertEquals('user1', $result);
		$result = $query->where(['name' => 'user1'])->scalar('noname', $this->getConnection());
		$this->assertNull($result);
		$result = $query->where(['name' => 'user5'])->scalar('name', $this->getConnection());
		$this->assertNull($result);
	}

	public function testColumn()
	{
		$query = new Query;
		$query->from('test', 'user');

		$result = $query->orderBy(['name' => SORT_ASC])->column('name', $this->getConnection());
		$this->assertEquals(['user1', 'user2', 'user3', 'user4'], $result);
		$result = $query->column('noname', $this->getConnection());
		$this->assertEquals([null, null, null, null], $result);
		$result = $query->where(['name' => 'user5'])->scalar('name', $this->getConnection());
		$this->assertNull($result);

	}


	public function testOrder()
	{
		$query = new Query;
		$query->orderBy('team');
		$this->assertEquals(['team' => SORT_ASC], $query->orderBy);

		$query->addOrderBy('company');
		$this->assertEquals(['team' => SORT_ASC, 'company' => SORT_ASC], $query->orderBy);

		$query->addOrderBy('age');
		$this->assertEquals(['team' => SORT_ASC, 'company' => SORT_ASC, 'age' => SORT_ASC], $query->orderBy);

		$query->addOrderBy(['age' => SORT_DESC]);
		$this->assertEquals(['team' => SORT_ASC, 'company' => SORT_ASC, 'age' => SORT_DESC], $query->orderBy);

		$query->addOrderBy('age ASC, company DESC');
		$this->assertEquals(['team' => SORT_ASC, 'company' => SORT_DESC, 'age' => SORT_ASC], $query->orderBy);
	}

	public function testLimitOffset()
	{
		$query = new Query;
		$query->limit(10)->offset(5);
		$this->assertEquals(10, $query->limit);
		$this->assertEquals(5, $query->offset);
	}

	public function testUnion()
	{
	}
}
