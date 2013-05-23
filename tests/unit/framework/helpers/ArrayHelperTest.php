<?php

namespace yiiunit\framework\helpers;

use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use yii\web\Sort;

class ArrayHelperTest extends \yii\test\TestCase
{
	public function testMerge()
	{


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
