<?php

namespace yiiunit\framework\base;

class Foo extends \yii\base\Object
{
	public $prop;
}

/**
 * ObjectTest
 */
class ObjectTest extends \yiiunit\TestCase
{
	protected $object;

	public function setUp()
	{
		$this->object = new NewObject;
	}

	public function tearDown()
	{
		$this->object = null;
	}

	public function testCreate()
	{
		$foo = Foo::create(array(
			'prop' => array(
				'test' => 'test',
			),
		));

		$this->assertEquals('test', $foo->prop['test']);
	}

	public function testHasProperty()
	{
		$this->assertTrue($this->object->hasProperty('Text'), "Component hasn't property Text");
		$this->assertTrue($this->object->hasProperty('text'), "Component hasn't property text");
		$this->assertFalse($this->object->hasProperty('Caption'), "Component as property Caption");
	}

	public function testCanGetProperty()
	{
		$this->assertTrue($this->object->canGetProperty('Text'));
		$this->assertTrue($this->object->canGetProperty('text'));
		$this->assertFalse($this->object->canGetProperty('Caption'));
	}

	public function testCanSetProperty()
	{
		$this->assertTrue($this->object->canSetProperty('Text'));
		$this->assertTrue($this->object->canSetProperty('text'));
		$this->assertFalse($this->object->canSetProperty('Caption'));
	}

	public function testGetProperty()
	{
		$this->assertTrue('default'===$this->object->Text);
		$this->setExpectedException('yii\base\Exception');
		$value2=$this->object->Caption;
	}

	public function testSetProperty()
	{
		$value='new value';
		$this->object->Text=$value;
		$text=$this->object->Text;
		$this->assertTrue($value===$this->object->Text);
		$this->setExpectedException('yii\base\Exception');
		$this->object->NewMember=$value;
	}

	public function testIsset()
	{
		$this->assertTrue(isset($this->object->Text));
		$this->assertTrue(!empty($this->object->Text));

		unset($this->object->Text);
		$this->assertFalse(isset($this->object->Text));
		$this->assertFalse(!empty($this->object->Text));

		$this->object->Text='';
		$this->assertTrue(isset($this->object->Text));
		$this->assertTrue(empty($this->object->Text));
	}


	public function testEvaluateExpression()
	{
		$object = new NewObject;
		$this->assertEquals('Hello world',$object->evaluateExpression('"Hello $who"',array('who' => 'world')));
		$this->assertEquals('Hello world',$object->evaluateExpression(array($object,'exprEvaluator'),array('who' => 'world')));
	}
}


class NewObject extends \yii\base\Component
{
	private $_object = null;
	private $_text = 'default';

	public function getText()
	{
		return $this->_text;
	}

	public function setText($value)
	{
		$this->_text=$value;
	}

	public function getObject()
	{
		if(!$this->_object)
		{
			$this->_object=new self;
			$this->_object->_text='object text';
		}
		return $this->_object;
	}

	public function exprEvaluator($p1,$comp)
	{
		return "Hello $p1";
	}
}