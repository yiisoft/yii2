<?php

namespace yiiunit\framework\helpers;

use yii\helpers\ArrayHelper;
use yii\test\TestCase;
use yii\data\Sort;

class ArrayHelperTest extends TestCase
{

    protected function getDataForColumnTest()
    {
        return array(
            array(
                'id' => 2135,
                'first_name' => 'John',
                'last_name' => 'Doe',
            ),
            array(
                'id' => 3245,
                'first_name' => 'Sally',
                'last_name' => 'Smith',
            ),
            array(
                'id' => 5342,
                'first_name' => 'Jane',
                'last_name' => 'Jones',
            ),
            array(
                'id' => 5623,
                'first_name' => 'Peter',
                'last_name' => 'Doe',
            )
        );
    }
    
    public function testMerge()
    {


    }

    public function testGetColumn()
    {
        $result = ArrayHelper::getColumn($this->getDataForColumnTest(), 'first_name');
        $this->assertEquals(array('John', 'Sally', 'Jane', 'Peter'), $result, "Documented behaviour on php.net");
        /* Missing functionality:
        $result = ArrayHelper::getColumn($this->getDataForColumnTest(), 'first_name', 'id');
        $this->assertEquals(array(2135 => 'John', 3245 => 'Sally', 5342 => 'Jane', 5623 => 'Peter'));
        */
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
