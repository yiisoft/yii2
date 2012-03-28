<?php

namespace yiiunit\framework\base;

function globalEventHandler($event)
{
	$event->sender->eventHandled = true;
}

function globalEventHandler2($event)
{
	$event->sender->eventHandled = true;
	$event->handled = true;
}

class ComponentTest extends \yiiunit\TestCase
{
	/**
	 * @var NewComponent
	 */
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
		$this->assertTrue('default' === $this->component->Text);
		$this->setExpectedException('yii\base\Exception');
		$value2 = $this->component->Caption;
	}

	public function testSetProperty()
	{
		$value = 'new value';
		$this->component->Text = $value;
		$text = $this->component->Text;
		$this->assertTrue($value === $this->component->Text);
		$this->setExpectedException('yii\base\Exception');
		$this->component->NewMember = $value;
	}

	public function testIsset()
	{
		$this->assertTrue(isset($this->component->Text));
		$this->assertTrue(!empty($this->component->Text));

		unset($this->component->Text);
		$this->assertFalse(isset($this->component->Text));
		$this->assertFalse(!empty($this->component->Text));

		$this->component->Text = '';
		$this->assertTrue(isset($this->component->Text));
		$this->assertTrue(empty($this->component->Text));
	}

	public function testOn()
	{
		$this->assertEquals(0, $this->component->getEventHandlers('click')->getCount());
		$this->component->on('click', 'foo');
		$this->assertEquals(1, $this->component->getEventHandlers('click')->getCount());
		$this->component->on('click', 'bar');
		$this->assertEquals(2, $this->component->getEventHandlers('click')->getCount());

		$this->component->getEventHandlers('click')->add('test');
		$this->assertEquals(3, $this->component->getEventHandlers('click')->getCount());
	}

	public function testOff()
	{
		$this->component->on('click', 'foo');
		$this->component->on('click', array($this->component, 'myEventHandler'));
		$this->assertEquals(2, $this->component->getEventHandlers('click')->getCount());

		$result = $this->component->off('click', 'foo');
		$this->assertTrue($result);
		$this->assertEquals(1, $this->component->getEventHandlers('click')->getCount());
		$result = $this->component->off('click', 'foo');
		$this->assertFalse($result);
		$this->assertEquals(1, $this->component->getEventHandlers('click')->getCount());
		$result = $this->component->off('click', array($this->component, 'myEventHandler'));
		$this->assertTrue($result);
		$this->assertEquals(0, $this->component->getEventHandlers('click')->getCount());
	}

	public function testTrigger()
	{
		$this->component->on('click', array($this->component, 'myEventHandler'));
		$this->assertFalse($this->component->eventHandled);
		$this->assertNull($this->component->event);
		$this->component->raiseEvent();
		$this->assertTrue($this->component->eventHandled);
		$this->assertEquals('click', $this->component->event->name);
		$this->assertEquals($this->component, $this->component->event->sender);
		$this->assertFalse($this->component->event->handled);

		$eventRaised = false;
		$this->component->on('click', function($event) use (&$eventRaised) {
			$eventRaised = true;
		});
		$this->component->raiseEvent();
		$this->assertTrue($eventRaised);
	}

	public function testHasEventHandlers()
	{
		$this->assertFalse($this->component->hasEventHandlers('click'));
		$this->component->on('click', 'foo');
		$this->assertTrue($this->component->hasEventHandlers('click'));
	}

	public function testStopEvent()
	{
		$component = new NewComponent;
		$component->on('click', 'yiiunit\framework\base\globalEventHandler2');
		$component->on('click', array($this->component, 'myEventHandler'));
		$component->raiseEvent();
		$this->assertTrue($component->eventHandled);
		$this->assertFalse($this->component->eventHandled);
	}

	public function testDetachBehavior()
	{
		$component = new NewComponent;
		$behavior = new NewBehavior;
		$component->attachBehavior('a', $behavior);
		$this->assertSame($behavior, $component->detachBehavior('a'));
	}

	public function testDetachingBehaviors()
	{
		$component = new NewComponent;
		$behavior = new NewBehavior;
		$component->attachBehavior('a', $behavior);
		$component->detachBehaviors();
		$this->setExpectedException('yii\base\Exception');
		$component->test();
	}

	public function testAsa()
	{
		$component = new NewComponent;
		$behavior = new NewBehavior;
		$component->attachBehavior('a', $behavior);
		$this->assertSame($behavior, $component->asa('a'));
	}

	public function testCreate()
	{
		$component = NewComponent2::newInstance(array('a' => 3), 1, 2);
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
	public $event;
	public $behaviorCalled = false;

	public function getText()
	{
		return $this->_text;
	}

	public function setText($value)
	{
		$this->_text = $value;
	}

	public function getObject()
	{
		if (!$this->_object) {
			$this->_object = new NewComponent;
			$this->_object->_text = 'object text';
		}
		return $this->_object;
	}

	public function myEventHandler($event)
	{
		$this->eventHandled = true;
		$this->event = $event;
	}

	public function raiseEvent()
	{
		$this->trigger('click', new \yii\base\Event($this));
	}
}

class NewBehavior extends \yii\base\Behavior
{
	public function test()
	{
		$this->owner->behaviorCalled = true;
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