<?php

namespace yiiunit\framework\base;

use yii\base\Vector;

class ListItem
{
	public $data='data';
}

class VectorTest extends \yiiunit\TestCase
{
	/**
	 * @var Vector
	 */
	protected $vector;
	protected $item1, $item2, $item3;

	public function setUp()
	{
		$this->vector=new Vector;
		$this->item1=new ListItem;
		$this->item2=new ListItem;
		$this->item3=new ListItem;
		$this->vector->add($this->item1);
		$this->vector->add($this->item2);
	}

	public function tearDown()
	{
		$this->vector=null;
		$this->item1=null;
		$this->item2=null;
		$this->item3=null;
	}

	public function testConstruct()
	{
		$a=array(1,2,3);
		$vector=new Vector($a);
		$this->assertEquals(3,$vector->getCount());
		$vector2=new Vector($this->vector);
		$this->assertEquals(2,$vector2->getCount());
	}

	public function testItemAt()
	{
		$a=array(1, 2, null, 4);
		$vector=new Vector($a);
		$this->assertEquals(1, $vector->itemAt(0));
		$this->assertEquals(2, $vector->itemAt(1));
		$this->assertNull($vector->itemAt(2));
		$this->assertEquals(4, $vector->itemAt(3));
	}

	public function testGetCount()
	{
		$this->assertEquals(2,$this->vector->getCount());
		$this->assertEquals(2,$this->vector->Count);
	}

	public function testAdd()
	{
		$this->vector->add(null);
		$this->vector->add($this->item3);
		$this->assertEquals(4,$this->vector->getCount());
		$this->assertEquals(3,$this->vector->indexOf($this->item3));
	}

	public function testInsertAt()
	{
		$this->vector->insertAt(0,$this->item3);
		$this->assertEquals(3,$this->vector->getCount());
		$this->assertEquals(2,$this->vector->indexOf($this->item2));
		$this->assertEquals(0,$this->vector->indexOf($this->item3));
		$this->assertEquals(1,$this->vector->indexOf($this->item1));
		$this->setExpectedException('yii\base\InvalidCallException');
		$this->vector->insertAt(4,$this->item3);
	}

	public function testRemove()
	{
		$this->vector->remove($this->item1);
		$this->assertEquals(1,$this->vector->getCount());
		$this->assertEquals(-1,$this->vector->indexOf($this->item1));
		$this->assertEquals(0,$this->vector->indexOf($this->item2));

		$this->assertEquals(false,$this->vector->remove($this->item1));

	}

	public function testRemoveAt()
	{
		$this->vector->add($this->item3);
		$this->vector->removeAt(1);
		$this->assertEquals(-1,$this->vector->indexOf($this->item2));
		$this->assertEquals(1,$this->vector->indexOf($this->item3));
		$this->assertEquals(0,$this->vector->indexOf($this->item1));
		$this->setExpectedException('yii\base\InvalidCallException');
		$this->vector->removeAt(2);
	}

	public function testClear()
	{
		$this->vector->add($this->item3);
		$this->vector->clear();
		$this->assertEquals(0,$this->vector->getCount());
		$this->assertEquals(-1,$this->vector->indexOf($this->item1));
		$this->assertEquals(-1,$this->vector->indexOf($this->item2));

		$this->vector->add($this->item3);
		$this->vector->clear(true);
		$this->assertEquals(0,$this->vector->getCount());
		$this->assertEquals(-1,$this->vector->indexOf($this->item1));
		$this->assertEquals(-1,$this->vector->indexOf($this->item2));
	}

	public function testContains()
	{
		$this->assertTrue($this->vector->contains($this->item1));
		$this->assertTrue($this->vector->contains($this->item2));
		$this->assertFalse($this->vector->contains($this->item3));
	}

	public function testIndexOf()
	{
		$this->assertEquals(0,$this->vector->indexOf($this->item1));
		$this->assertEquals(1,$this->vector->indexOf($this->item2));
		$this->assertEquals(-1,$this->vector->indexOf($this->item3));
	}

	public function testFromArray()
	{
		$array=array($this->item3,$this->item1);
		$this->vector->copyFrom($array);
		$this->assertTrue(count($array)==2 && $this->vector[0]===$this->item3 && $this->vector[1]===$this->item1);
		$this->setExpectedException('yii\base\InvalidCallException');
		$this->vector->copyFrom($this);
	}

	public function testMergeWith()
	{
		$array=array($this->item3,$this->item1);
		$this->vector->mergeWith($array);
		$this->assertTrue($this->vector->getCount()==4 && $this->vector[0]===$this->item1 && $this->vector[3]===$this->item1);

		$a=array(1);
		$vector=new Vector($a);
		$this->vector->mergeWith($vector);
		$this->assertTrue($this->vector->getCount()==5 && $this->vector[0]===$this->item1 && $this->vector[3]===$this->item1 && $this->vector[4]===1);

		$this->setExpectedException('yii\base\InvalidCallException');
		$this->vector->mergeWith($this);
	}

	public function testToArray()
	{
		$array=$this->vector->toArray();
		$this->assertTrue(count($array)==2 && $array[0]===$this->item1 && $array[1]===$this->item2);
	}

	public function testArrayRead()
	{
		$this->assertTrue($this->vector[0]===$this->item1);
		$this->assertTrue($this->vector[1]===$this->item2);
		$this->setExpectedException('yii\base\InvalidCallException');
		$a=$this->vector[2];
	}

	public function testGetIterator()
	{
		$n=0;
		$found=0;
		foreach($this->vector as $index=>$item)
		{
			foreach($this->vector as $a=>$b);	// test of iterator
			$n++;
			if($index===0 && $item===$this->item1)
				$found++;
			if($index===1 && $item===$this->item2)
				$found++;
		}
		$this->assertTrue($n==2 && $found==2);
	}

	public function testArrayMisc()
	{
		$this->assertEquals($this->vector->Count,count($this->vector));
		$this->assertTrue(isset($this->vector[1]));
		$this->assertFalse(isset($this->vector[2]));
	}

	public function testOffsetSetAdd()
	{
		$vector = new Vector(array(1, 2, 3));
		$vector->offsetSet(null, 4);
		$this->assertEquals(array(1, 2, 3, 4), $vector->toArray());
	}

	public function testOffsetSetReplace()
	{
		$vector = new Vector(array(1, 2, 3));
		$vector->offsetSet(1, 4);
		$this->assertEquals(array(1, 4, 3), $vector->toArray());
	}

	public function testOffsetUnset()
	{
		$vector = new Vector(array(1, 2, 3));
		$vector->offsetUnset(1);
		$this->assertEquals(array(1, 3), $vector->toArray());
	}

	public function testIteratorCurrent()
	{
		$vector = new Vector(array('value1', 'value2'));
		$val = $vector->getIterator()->current();
		$this->assertEquals('value1', $val);
	}
}
