<?php

namespace yiiunit\framework\base;

function globalEventHandler($event)
{
	$event->sender->eventHandled=true;
}

function globalEventHandler2($event)
{
	$event->sender->eventHandled=true;
	$event->handled=true;
}

class ComponentTest extends \yiiunit\TestCase
{
	protected $component;

	public function setUp()
	{
		$this->component = new NewComponent();
	}

	public function tearDown()
	{
		$this->component = null;
	}

	public function testHasProperty()
	{
		$this->assertTrue($this->component->hasProperty('Text'), "Component hasn't property Text");
		$this->assertTrue($this->component->hasProperty('text'), "Component hasn't property text");
		$this->assertFalse($this->component->hasProperty('Caption'), "Component as property Caption");
	}

	public function testCanGetProperty()
	{
		$this->assertTrue($this->component->canGetProperty('Text'));
		$this->assertTrue($this->component->canGetProperty('text'));
		$this->assertFalse($this->component->canGetProperty('Caption'));
	}

	public function testCanSetProperty()
	{
		$this->assertTrue($this->component->canSetProperty('Text'));
		$this->assertTrue($this->component->canSetProperty('text'));
		$this->assertFalse($this->component->canSetProperty('Caption'));
	}

	public function testGetProperty()
	{
		$this->assertTrue('default'===$this->component->Text);
		$this->setExpectedException('yii\base\Exception');
		$value2=$this->component->Caption;
	}

	public function testSetProperty()
	{
		$value='new value';
		$this->component->Text=$value;
		$text=$this->component->Text;
		$this->assertTrue($value===$this->component->Text);
		$this->setExpectedException('yii\base\Exception');
		$this->component->NewMember=$value;
	}

	public function testIsset()
	{
		$this->assertTrue(isset($this->component->Text));
		$this->assertTrue(!empty($this->component->Text));

		unset($this->component->Text);
		$this->assertFalse(isset($this->component->Text));
		$this->assertFalse(!empty($this->component->Text));

		$this->component->Text='';
		$this->assertTrue(isset($this->component->Text));
		$this->assertTrue(empty($this->component->Text));
	}

	public function testHasEvent()
	{
		$this->assertTrue($this->component->hasEvent('OnMyEvent'));
		$this->assertTrue($this->component->hasEvent('onmyevent'));
		$this->assertFalse($this->component->hasEvent('onYourEvent'));
	}

	public function testHasEventHandlers()
	{
		$this->assertFalse($this->component->hasEventHandlers('OnMyEvent'));
		$this->component->attachEventHandler('OnMyEvent','foo');
		$this->assertTrue($this->component->hasEventHandlers('OnMyEvent'));
	}

	public function testGetEventHandlers()
	{
		$list=$this->component->getEventHandlers('OnMyEvent');
		$this->assertEquals($list->getCount(),0);
		$this->component->attachEventHandler('OnMyEvent','foo');
		$this->assertEquals($list->getCount(),1);
		$this->setExpectedException('yii\base\Exception');
		$list=$this->component->getEventHandlers('YourEvent');
	}

	public function testAttachEventHandler()
	{
		$this->component->attachEventHandler('OnMyEvent','foo');
		$this->assertTrue($this->component->getEventHandlers('OnMyEvent')->getCount()===1);
		$this->setExpectedException('yii\base\Exception');
		$this->component->attachEventHandler('YourEvent','foo');
	}

	public function testDettachEventHandler()
	{
		$this->component->attachEventHandler('OnMyEvent','foo');
		$this->component->attachEventHandler('OnMyEvent',array($this->component,'myEventHandler'));
		$this->assertEquals($this->component->getEventHandlers('OnMyEvent')->getCount(),2);

		$this->assertTrue($this->component->detachEventHandler('OnMyEvent','foo'));
		$this->assertEquals($this->component->getEventHandlers('OnMyEvent')->getCount(),1);

		$this->assertFalse($this->component->detachEventHandler('OnMyEvent','foo'));
		$this->assertEquals($this->component->getEventHandlers('OnMyEvent')->getCount(),1);

		$this->assertTrue($this->component->detachEventHandler('OnMyEvent',array($this->component,'myEventHandler')));
		$this->assertEquals($this->component->getEventHandlers('OnMyEvent')->getCount(),0);

		$this->assertFalse($this->component->detachEventHandler('OnMyEvent','foo'));
	}

	public function testRaiseEvent()
	{
		$this->component->attachEventHandler('OnMyEvent',array($this->component,'myEventHandler'));
		$this->assertFalse($this->component->eventHandled);
		$this->component->raiseEvent('OnMyEvent',new \yii\base\Event($this));
		$this->assertTrue($this->component->eventHandled);

		$this->setExpectedException('yii\base\Exception');
		$this->component->raiseEvent('OnUnknown',new \yii\base\Event($this));
	}

	public function testEventAccessor()
	{
		$component=new NewComponent;
		$this->assertEquals($component->onMyEvent->getCount(),0);
		$component->onMyEvent='yiiunit\framework\base\globalEventHandler';
		$component->onMyEvent=array($this->component,'myEventHandler');
		$this->assertEquals($component->onMyEvent->getCount(),2);
		$this->assertFalse($component->eventHandled);
		$this->assertFalse($this->component->eventHandled);
		$component->onMyEvent();
		$this->assertTrue($component->eventHandled);
		$this->assertTrue($this->component->eventHandled);
	}

	public function testStopEvent()
	{
		$component=new NewComponent;
		$component->onMyEvent='yiiunit\framework\base\globalEventHandler2';
		$component->onMyEvent=array($this->component,'myEventHandler');
		$component->onMyEvent();
		$this->assertTrue($component->eventHandled);
		$this->assertFalse($this->component->eventHandled);
	}

	public function testInvalidHandler1()
	{
		$this->component->onMyEvent=array(1,2,3);
		$this->setExpectedException('yii\base\Exception');
		$this->component->onMyEvent();
	}

	public function testInvalidHandler2()
	{
		$this->component->onMyEvent=array($this->component,'nullHandler');
		$this->setExpectedException('yii\base\Exception');
		$this->component->onMyEvent();
	}

	public function testDetachBehavior()
	{
		$component=new NewComponent;
		$behavior = new NewBehavior;
		$component->attachBehavior('a',$behavior);
		$this->assertSame($behavior,$component->detachBehavior('a'));
	}

	public function testDetachingBehaviors()
	{
		$component=new NewComponent;
		$behavior = new NewBehavior;
		$component->attachBehavior('a',$behavior);
		$component->detachBehaviors();
		$this->setExpectedException('yii\base\Exception');
		$component->test();
	}

	public function testAsa()
	{
		$component=new NewComponent;
		$behavior = new NewBehavior;
		$component->attachBehavior('a',$behavior);
		$this->assertSame($behavior,$component->asa('a'));
	}

	public function testCreate()
	{
		$component = NewComponent2::newInstance(array('a'=>3), 1, 2);
		$this->assertEquals(1, $component->b);
		$this->assertEquals(2, $component->c);
		$this->assertEquals(3, $component->a);
	}
}

class NewComponent extends \yii\base\Component
{
	private $_object = null;
	private $_text = 'default';
	public $eventHandled = false;
	public $behaviorCalled = false;

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
			$this->_object=new NewComponent;
			$this->_object->_text='object text';
		}
		return $this->_object;
	}

	public function onMyEvent()
	{
		$this->raiseEvent('OnMyEvent',new \yii\base\Event($this));
	}

	public function myEventHandler($event)
	{
		$this->eventHandled=true;
	}
	public function exprEvaluator($p1,$comp) {
		return "Hello $p1";
	}
}

class NewBehavior extends \yii\base\Behavior
{
	public function test()
	{
		$this->owner->behaviorCalled=true;
		return 2;
	}
}

class NewComponent2 extends \yii\base\Component
{
	public $a;
	public $b;
	public $c;
	public function __construct($b, $c)
	{
		$this->b = $b;
		$this->c = $c;
	}
}