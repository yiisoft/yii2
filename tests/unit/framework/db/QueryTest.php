<?php

namespace yiiunit\framework\db;

use yii\db\Connection;
use yii\db\Command;
use yii\db\Query;
use yii\db\DataReader;

class QueryTest extends \yiiunit\DatabaseTestCase
{
	function testSelect()
	{
		// default
		$query = new Query;
		$query->select('*');
		$this->assertEquals(array('*'), $query->select);
		$this->assertNull($query->distinct);
		$this->assertEquals(null, $query->selectOption);

		$query = new Query;
		$query->select('id, name', 'something')->distinct(true);
		$this->assertEquals(array('id', 'name'), $query->select);
		$this->assertTrue($query->distinct);
		$this->assertEquals('something', $query->selectOption);
	}

	function testFrom()
	{
		$query = new Query;
		$query->from('tbl_user');
		$this->assertEquals(array('tbl_user'), $query->from);
	}

	function testWhere()
	{
		$query = new Query;
		$query->where('id = :id', array(':id' => 1));
		$this->assertEquals('id = :id', $query->where);
		$this->assertEquals(array(':id' => 1), $query->params);

		$query->andWhere('name = :name', array(':name' => 'something'));
		$this->assertEquals(array('and', 'id = :id', 'name = :name'), $query->where);
		$this->assertEquals(array(':id' => 1, ':name' => 'something'), $query->params);

		$query->orWhere('age = :age', array(':age' => '30'));
		$this->assertEquals(array('or', array('and', 'id = :id', 'name = :name'), 'age = :age'), $query->where);
		$this->assertEquals(array(':id' => 1, ':name' => 'something', ':age' => '30'), $query->params);
	}

	function testJoin()
	{

	}

	function testGroup()
	{
		$query = new Query;
		$query->groupBy('team');
		$this->assertEquals(array('team'), $query->groupBy);

		$query->addGroupBy('company');
		$this->assertEquals(array('team', 'company'), $query->groupBy);

		$query->addGroupBy('age');
		$this->assertEquals(array('team', 'company', 'age'), $query->groupBy);
	}

	function testHaving()
	{
		$query = new Query;
		$query->having('id = :id', array(':id' => 1));
		$this->assertEquals('id = :id', $query->having);
		$this->assertEquals(array(':id' => 1), $query->params);

		$query->andHaving('name = :name', array(':name' => 'something'));
		$this->assertEquals(array('and', 'id = :id', 'name = :name'), $query->having);
		$this->assertEquals(array(':id' => 1, ':name' => 'something'), $query->params);

		$query->orHaving('age = :age', array(':age' => '30'));
		$this->assertEquals(array('or', array('and', 'id = :id', 'name = :name'), 'age = :age'), $query->having);
		$this->assertEquals(array(':id' => 1, ':name' => 'something', ':age' => '30'), $query->params);
	}

	function testOrder()
	{
		$query = new Query;
		$query->orderBy('team');
		$this->assertEquals(array('team' => false), $query->orderBy);

		$query->addOrderBy('company');
		$this->assertEquals(array('team' => false, 'company' => false), $query->orderBy);

		$query->addOrderBy('age');
		$this->assertEquals(array('team' => false, 'company' => false, 'age' => false), $query->orderBy);

		$query->addOrderBy(array('age' => true));
		$this->assertEquals(array('team' => false, 'company' => false, 'age' => true), $query->orderBy);

		$query->addOrderBy('age ASC, company DESC');
		$this->assertEquals(array('team' => false, 'company' => true, 'age' => false), $query->orderBy);
	}

	function testLimitOffset()
	{
		$query = new Query;
		$query->limit(10)->offset(5);
		$this->assertEquals(10, $query->limit);
		$this->assertEquals(5, $query->offset);
	}

	function testUnion()
	{

	}
}
