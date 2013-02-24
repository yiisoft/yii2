<?php

namespace yiiunit\framework\base;

use yii\base\Dictionary;

class MapItem
{
	public $data='data';
}

class DictionaryTest extends \yiiunit\TestCase
{
	/**
	 * @var \yii\base\Dictionary
	 */
	protected $dictionary;
	protected $item1,$item2,$item3;

	public function setUp()
	{
		$this->dictionary=new Dictionary;
		$this->item1=new MapItem;
		$this->item2=new MapItem;
		$this->item3=new MapItem;
		$this->dictionary->add('key1',$this->item1);
		$this->dictionary->add('key2',$this->item2);
	}

	public function tearDown()
	{
		$this->dictionary=null;
		$this->item1=null;
		$this->item2=null;
		$this->item3=null;
	}

	public function testConstruct()
	{
		$a=array(1,2,'key3'=>3);
		$dictionary=new Dictionary($a);
		$this->assertEquals(3,$dictionary->getCount());
		$dictionary2=new Dictionary($this->dictionary);
		$this->assertEquals(2,$dictionary2->getCount());
	}

	public function testGetCount()
	{
		$this->assertEquals(2,$this->dictionary->getCount());
	}

	public function testGetKeys()
	{
		$keys=$this->dictionary->getKeys();
		$this->assertEquals(2,count($keys));
		$this->assertEquals('key1',$keys[0]);
		$this->assertEquals('key2',$keys[1]);
	}

	public function testAdd()
	{
		$this->dictionary->add('key3',$this->item3);
		$this->assertEquals(3,$this->dictionary->getCount());
		$this->assertTrue($this->dictionary->contains('key3'));

		$this->dictionary[] = 'test';
	}

	public function testRemove()
	{
		$this->dictionary->remove('key1');
		$this->assertEquals(1,$this->dictionary->getCount());
		$this->assertTrue(!$this->dictionary->contains('key1'));
		$this->assertTrue($this->dictionary->remove('unknown key')===null);
	}

	public function testClear()
	{
		$this->dictionary->add('key3',$this->item3);
		$this->dictionary->clear();
		$this->assertEquals(0,$this->dictionary->getCount());
		$this->assertTrue(!$this->dictionary->contains('key1') && !$this->dictionary->contains('key2'));

		$this->dictionary->add('key3',$this->item3);
		$this->dictionary->clear(true);
		$this->assertEquals(0,$this->dictionary->getCount());
		$this->assertTrue(!$this->dictionary->contains('key1') && !$this->dictionary->contains('key2'));
	}

	public function testContains()
	{
		$this->assertTrue($this->dictionary->contains('key1'));
		$this->assertTrue($this->dictionary->contains('key2'));
		$this->assertFalse($this->dictionary->contains('key3'));
	}

	public function testFromArray()
	{
		$array=array('key3'=>$this->item3,'key4'=>$this->item1);
		$this->dictionary->copyFrom($array);

		$this->assertEquals(2, $this->dictionary->getCount());
		$this->assertEquals($this->item3, $this->dictionary['key3']);
		$this->assertEquals($this->item1, $this->dictionary['key4']);

		$this->setExpectedException('yii\base\InvalidCallException');
		$this->dictionary->copyFrom($this);
	}

	public function testMergeWith()
	{
		$a=array('a'=>'v1','v2',array('2'),'c'=>array('3','c'=>'a'));
		$b=array('v22','a'=>'v11',array('2'),'c'=>array('c'=>'3','a'));
		$c=array('a'=>'v11','v2',array('2'),'c'=>array('3','c'=>'3','a'),'v22',array('2'));
		$dictionary=new Dictionary($a);
		$dictionary2=new Dictionary($b);
		$dictionary->mergeWith($dictionary2);
		$this->assertTrue($dictionary->toArray()===$c);

		$array=array('key2'=>$this->item1,'key3'=>$this->item3);
		$this->dictionary->mergeWith($array,false);
		$this->assertEquals(3,$this->dictionary->getCount());
		$this->assertEquals($this->item1,$this->dictionary['key2']);
		$this->assertEquals($this->item3,$this->dictionary['key3']);
		$this->setExpectedException('yii\base\InvalidCallException');
		$this->dictionary->mergeWith($this,false);
	}

	public function testRecursiveMergeWithTraversable(){
		$dictionary = new Dictionary();
		$obj = new \ArrayObject(array(
			'k1' => $this->item1,
			'k2' => $this->item2,
			'k3' => new \ArrayObject(array(
				'k4' => $this->item3,
			))
		));
		$dictionary->mergeWith($obj,true);

		$this->assertEquals(3, $dictionary->getCount());
		$this->assertEquals($this->item1, $dictionary['k1']);
		$this->assertEquals($this->item2, $dictionary['k2']);
		$this->assertEquals($this->item3, $dictionary['k3']['k4']);
	}

	public function testArrayRead()
	{
		$this->assertEquals($this->item1,$this->dictionary['key1']);
		$this->assertEquals($this->item2,$this->dictionary['key2']);
		$this->assertEquals(null,$this->dictionary['key3']);
	}

	public function testArrayWrite()
	{
		$this->dictionary['key3']=$this->item3;
		$this->assertEquals(3,$this->dictionary->getCount());
		$this->assertEquals($this->item3,$this->dictionary['key3']);

		$this->dictionary['key1']=$this->item3;
		$this->assertEquals(3,$this->dictionary->getCount());
		$this->assertEquals($this->item3,$this->dictionary['key1']);

		unset($this->dictionary['key2']);
		$this->assertEquals(2,$this->dictionary->getCount());
		$this->assertTrue(!$this->dictionary->contains('key2'));

		unset($this->dictionary['unknown key']);
	}

	public function testArrayForeach()
	{
		$n=0;
		$found=0;
		foreach($this->dictionary as $index=>$item)
		{
			$n++;
			if($index==='key1' && $item===$this->item1)
				$found++;
			if($index==='key2' && $item===$this->item2)
				$found++;
		}
		$this->assertTrue($n==2 && $found==2);
	}

	public function testArrayMisc()
	{
		$this->assertEquals($this->dictionary->Count,count($this->dictionary));
		$this->assertTrue(isset($this->dictionary['key1']));
		$this->assertFalse(isset($this->dictionary['unknown key']));
	}

	public function testToArray()
	{
		$dictionary = new Dictionary(array('key' => 'value'));
		$this->assertEquals(array('key' => 'value'), $dictionary->toArray());
	}

	public function testIteratorCurrent()
	{
		$dictionary = new Dictionary(array('key1' => 'value1', 'key2' => 'value2'));
		$val = $dictionary->getIterator()->current();
		$this->assertEquals('value1', $val);
	}
}
