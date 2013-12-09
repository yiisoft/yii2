<?php

namespace yiiunit\extensions\mongodb;

use yii\mongodb\Query;

/**
 * @group mongodb
 */
class QueryTest extends MongoDbTestCase
{
	public function testSelect()
	{
		// default
		$query = new Query;
		$select = [];
		$query->select($select);
		$this->assertEquals($select, $query->select);

		$query = new Query;
		$select = ['name', 'something'];
		$query->select($select);
		$this->assertEquals($select, $query->select);
	}

	public function testFrom()
	{
		$query = new Query;
		$from = 'customer';
		$query->from($from);
		$this->assertEquals($from, $query->from);

		$query = new Query;
		$from = ['', 'customer'];
		$query->from($from);
		$this->assertEquals($from, $query->from);
	}

	public function testWhere()
	{
		$query = new Query;
		$query->where(['name' => 'name1']);
		$this->assertEquals(['name' => 'name1'], $query->where);

		$query->andWhere(['address' => 'address1']);
		$this->assertEquals(
			[
				'and',
				['name' => 'name1'],
				['address' => 'address1']
			],
			$query->where
		);

		$query->orWhere(['name' => 'name2']);
		$this->assertEquals(
			[
				'or',
				[
					'and',
					['name' => 'name1'],
					['address' => 'address1']
				],
				['name' => 'name2']

			],
			$query->where
		);
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
}