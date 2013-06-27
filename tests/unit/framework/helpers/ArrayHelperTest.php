<?php

namespace yiiunit\framework\helpers;

use yii\data\Sort;
use yii\helpers\ArrayHelper;
use yii\test\TestCase;

class ArrayHelperTest extends TestCase
{

	public function testMerge()
	{


	}

	public function testGetColumn()
	{
		$this->assertFalse(ArrayHelper::getColumn($this->getDataForColumnTest(), null)); //todo WIP
		$result = ArrayHelper::getColumn($this->getDataForColumnTest(), 'first_name');
		// default
		$this->assertEquals(
			array('John', 'Sally', 'Jane'),
			$result,
			"Documented behaviour on php.net / array_column_basic.phpt in php source"
		);
		// yii-specific
		$result = ArrayHelper::getColumn($this->getDataForColumnTest2(), 'first_name', true);
		$this->assertEquals(
			array(2 => 'John', 3 => 'Sally', 4 => 'Jane'),
			$result
		);
		/* Missing functionality: */
		$result = ArrayHelper::getColumn($this->getDataForColumnTest(), 'first_name', 'id');
		$this->assertEquals(array(1 => 'John', 2 => 'Sally', 3 => 'Jane'), $result);
		$data = $this->getDataForColumnTest();
		$result = ArrayHelper::getColumn($data, null, 'id');
		$this->assertEquals(
			array(1 => $data[0], 2 => $data[1], 3 => $data[2]),
			$result,
			"name: null behaviour => return full array"
		);
		if (version_compare(phpversion(), '5.5.0', '>=')) {
			$should = array_column($this->getDataForColumnTest(), 'first_name');
			$is = ArrayHelper::getColumn($this->getDataForColumnTest(), 'first_name');
			$this->assertEquals($should, $is, "Comparing with official php implementation: default behaviour");
			$should = array_column($this->getDataForColumnTest(), 'first_name', 'id');
			$is = ArrayHelper::getColumn($this->getDataForColumnTest(), 'first_name', 'id');
			$this->assertEquals($should, $is, "Comparing with official php implementation: index by field");
			$should = array_column($this->getDataForColumnTest(), null, 'id');
			$is = ArrayHelper::getColumn($this->getDataForColumnTest(), null, 'id');
			$this->assertEquals($should, $is, "Comparing with official php implementation: nulled name");
		}

		// yii-specific: anonymous
		$is = ArrayHelper::getColumn(
			$this->getDataForColumnTest(),
			function ($element) {
				return $element['first_name'];
			}
		);
		$should = array('John', 'Sally', 'Jane');
		$this->assertEquals($should, $is);
		// mixed together
		$is = ArrayHelper::getColumn(
			$this->getDataForColumnTest(),
			function ($element) {
				return $element['first_name'];
			},
			'id'
		);
		$should = array(1 => 'John', 2 => 'Sally', 3 => 'Jane');
		$this->assertEquals($should, $is);
	}

	protected function getDataForColumnTest()
	{
		return array(
			array(
				'id' => 1,
				'first_name' => 'John',
				'last_name' => 'Doe'
			),
			array(
				'id' => 2,
				'first_name' => 'Sally',
				'last_name' => 'Smith'
			),
			array(
				'id' => 3,
				'first_name' => 'Jane',
				'last_name' => 'Jones'
			)
		);
	}

	protected function getDataForColumnTest2()
	{
		return array(
			2 => array(
				'id' => 1,
				'first_name' => 'John',
				'last_name' => 'Doe'
			),
			3 => array(
				'id' => 2,
				'first_name' => 'Sally',
				'last_name' => 'Smith'
			),
			4 => array(
				'id' => 3,
				'first_name' => 'Jane',
				'last_name' => 'Jones'
			)
		);
	}

	public function testRemove()
	{
		$array = array('name' => 'b', 'age' => 3);
		$name = ArrayHelper::remove($array, 'name');

		$this->assertEquals($name, 'b');
		$this->assertEquals($array, array('age' => 3));
	}

	public function testMultisort()
	{
		// single key
		$array = array(
			array('name' => 'b', 'age' => 3),
			array('name' => 'a', 'age' => 1),
			array('name' => 'c', 'age' => 2),
		);
		ArrayHelper::multisort($array, 'name');
		$this->assertEquals(array('name' => 'a', 'age' => 1), $array[0]);
		$this->assertEquals(array('name' => 'b', 'age' => 3), $array[1]);
		$this->assertEquals(array('name' => 'c', 'age' => 2), $array[2]);

		// multiple keys
		$array = array(
			array('name' => 'b', 'age' => 3),
			array('name' => 'a', 'age' => 2),
			array('name' => 'a', 'age' => 1),
		);
		ArrayHelper::multisort($array, array('name', 'age'));
		$this->assertEquals(array('name' => 'a', 'age' => 1), $array[0]);
		$this->assertEquals(array('name' => 'a', 'age' => 2), $array[1]);
		$this->assertEquals(array('name' => 'b', 'age' => 3), $array[2]);

		// case-insensitive
		$array = array(
			array('name' => 'a', 'age' => 3),
			array('name' => 'b', 'age' => 2),
			array('name' => 'B', 'age' => 4),
			array('name' => 'A', 'age' => 1),
		);

		ArrayHelper::multisort($array, array('name', 'age'), false, array(SORT_STRING, SORT_REGULAR));
		$this->assertEquals(array('name' => 'A', 'age' => 1), $array[0]);
		$this->assertEquals(array('name' => 'B', 'age' => 4), $array[1]);
		$this->assertEquals(array('name' => 'a', 'age' => 3), $array[2]);
		$this->assertEquals(array('name' => 'b', 'age' => 2), $array[3]);

		ArrayHelper::multisort($array, array('name', 'age'), false, array(SORT_STRING, SORT_REGULAR), false);
		$this->assertEquals(array('name' => 'A', 'age' => 1), $array[0]);
		$this->assertEquals(array('name' => 'a', 'age' => 3), $array[1]);
		$this->assertEquals(array('name' => 'b', 'age' => 2), $array[2]);
		$this->assertEquals(array('name' => 'B', 'age' => 4), $array[3]);
	}

	public function testMultisortUseSort()
	{
		// single key
		$sort = new Sort();
		$sort->attributes = array('name', 'age');
		$sort->defaults = array('name' => Sort::ASC);
		$orders = $sort->getOrders();

		$array = array(
			array('name' => 'b', 'age' => 3),
			array('name' => 'a', 'age' => 1),
			array('name' => 'c', 'age' => 2),
		);
		ArrayHelper::multisort($array, array_keys($orders), array_values($orders));
		$this->assertEquals(array('name' => 'a', 'age' => 1), $array[0]);
		$this->assertEquals(array('name' => 'b', 'age' => 3), $array[1]);
		$this->assertEquals(array('name' => 'c', 'age' => 2), $array[2]);

		// multiple keys
		$sort = new Sort();
		$sort->attributes = array('name', 'age');
		$sort->defaults = array('name' => Sort::ASC, 'age' => Sort::DESC);
		$orders = $sort->getOrders();

		$array = array(
			array('name' => 'b', 'age' => 3),
			array('name' => 'a', 'age' => 2),
			array('name' => 'a', 'age' => 1),
		);
		ArrayHelper::multisort($array, array_keys($orders), array_values($orders));
		$this->assertEquals(array('name' => 'a', 'age' => 2), $array[0]);
		$this->assertEquals(array('name' => 'a', 'age' => 1), $array[1]);
		$this->assertEquals(array('name' => 'b', 'age' => 3), $array[2]);
	}
}
