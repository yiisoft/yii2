<?php

namespace yiiunit\framework\helpers;

use yii\base\Object;
use yii\helpers\ArrayHelper;
use yii\test\TestCase;
use yii\data\Sort;

class Post1
{
	public $id = 23;
	public $title = 'tt';
}

class Post2 extends Object
{
	public $id = 123;
	public $content = 'test';
	private $secret = 's';
	public function getSecret()
	{
		return $this->secret;
	}
}

class ArrayHelperTest extends TestCase
{
	public function testToArray()
	{
		$object = new Post1;
		$this->assertEquals(get_object_vars($object), ArrayHelper::toArray($object));
		$object = new Post2;
		$this->assertEquals(get_object_vars($object), ArrayHelper::toArray($object));

		$object1 = new Post1;
		$object2 = new Post2;
		$this->assertEquals(array(
			get_object_vars($object1),
			get_object_vars($object2),
		), ArrayHelper::toArray(array(
			$object1,
			$object2,
		)));

		$object = new Post2;
		$this->assertEquals(array(
			'id' => 123,
			'secret' => 's',
			'_content' => 'test',
			'length' => 4,
		), ArrayHelper::toArray($object, array(
			$object->className() => array(
				'id', 'secret',
				'_content' => 'content',
				'length' => function ($post) {
					return strlen($post->content);
				}
		))));
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
		$sort = new Sort(array(
			'attributes' => array('name', 'age'),
			'defaultOrder' => array('name' => Sort::ASC),
		));
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
		$sort = new Sort(array(
			'attributes' => array('name', 'age'),
			'defaultOrder' => array('name' => Sort::ASC, 'age' => Sort::DESC),
		));
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
