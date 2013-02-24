<?php
namespace yiiunit\framework\base;

use yii\base\Behavior;
use yii\base\Component;
use yii\base\Event;
use yiiunit\TestCase;

function globalEventHandler($event)
{
	$event->sender->eventHandled = true;
}

function globalEventHandler2($event)
{
	$event->sender->eventHandled = true;
	$event->handled = true;
}

class ComponentTest extends TestCase
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

	public function testClone()
	{
		$component = new NewComponent();
		$behavior = new NewBehavior();
		$component->attachBehavior('a', $behavior);
		$this->assertSame($behavior, $component->getBehavior('a'));
		$component->on('test', 'fake');
		$this->assertEquals(1, $component->getEventHandlers('test')->count);

		$clone = clone $component;
		$this->assertNotSame($component, $clone);
		$this->assertNull($clone->getBehavior('a'));
		$this->assertEquals(0, $clone->getEventHandlers('test')->count);
	}
	
	public function testHasProperty()
	{
		$this->assertTrue($this->component->hasProperty('Text'));
		$this->assertTrue($this->component->hasProperty('text'));
		$this->assertFalse($this->component->hasProperty('Caption'));
		$this->assertTrue($this->component->hasProperty('content'));
		$this->assertFalse($this->component->hasProperty('content', false));
		$this->assertFalse($this->component->hasProperty('Content'));
	}

	public function testCanGetProperty()
	{
		$this->assertTrue($this->component->canGetProperty('Text'));
		$this->assertTrue($this->component->canGetProperty('text'));
		$this->assertFalse($this->component->canGetProperty('Caption'));
		$this->assertTrue($this->component->canGetProperty('content'));
		$this->assertFalse($this->component->canGetProperty('content', false));
		$this->assertFalse($this->component->canGetProperty('Content'));
	}

	public function testCanSetProperty()
	{
		$this->assertTrue($this->component->canSetProperty('Text'));
		$this->assertTrue($this->component->canSetProperty('text'));
		$this->assertFalse($this->component->canSetProperty('Object'));
		$this->assertFalse($this->component->canSetProperty('Caption'));
		$this->assertTrue($this->component->canSetProperty('content'));
		$this->assertFalse($this->component->canSetProperty('content', false));
		$this->assertFalse($this->component->canSetProperty('Content'));

		// behavior
		$this->assertFalse($this->component->canSetProperty('p2'));
		$behavior = new NewBehavior();
		$this->component->attachBehavior('a', $behavior);
		$this->assertTrue($this->component->canSetProperty('p2'));
		$this->component->detachBehavior('a');
	}

	public function testGetProperty()
	{
		$this->assertTrue('default' === $this->component->Text);
		$this->setExpectedException('yii\base\UnknownPropertyException');
		$value2 = $this->component->Caption;
	}

	public function testSetProperty()
	{
		$value = 'new value';
		$this->component->Text = $value;
		$this->assertEquals($value, $this->component->Text);
		$this->setExpectedException('yii\base\UnknownPropertyException');
		$this->component->NewMember = $value;
	}

	public function testIsset()
	{
		$this->assertTrue(isset($this->component->Text));
		$this->assertFalse(empty($this->component->Text));

		$this->component->Text = '';
		$this->assertTrue(isset($this->component->Text));
		$this->assertTrue(empty($this->component->Text));

		$this->component->Text = null;
		$this->assertFalse(isset($this->component->Text));
		$this->assertTrue(empty($this->component->Text));


		$this->assertFalse(isset($this->component->p2));
		$this->component->attachBehavior('a', new NewBehavior());
		$this->component->setP2('test');
		$this->assertTrue(isset($this->component->p2));
	}

	public function testUnset()
	{
		unset($this->component->Text);
		$this->assertFalse(isset($this->component->Text));
		$this->assertTrue(empty($this->component->Text));

		$this->component->attachBehavior('a', new NewBehavior());
		$this->component->setP2('test');
		$this->assertEquals('test', $this->component->getP2());

		unset($this->component->p2);
		$this->assertNull($this->component->getP2());
	}

	public function testUnsetReadonly()
	{
		$this->setExpectedException('yii\base\InvalidCallException');
		unset($this->component->object);
	}

	public function testOn()
	{
		$this->assertEquals(0, $this->component->getEventHandlers('click')->getCount());
		$this->component->on('click', 'foo');
		$this->assertEquals(1, $this->component->getEventHandlers('click')->getCount());
		$this->component->on('click', 'bar');
		$this->assertEquals(2, $this->component->getEventHandlers('click')->getCount());
		$p = 'on click';
		$this->component->$p = 'foo2';
		$this->assertEquals(3, $this->component->getEventHandlers('click')->getCount());

		$this->component->getEventHandlers('click')->add('test');
		$this->assertEquals(4, $this->component->getEventHandlers('click')->getCount());
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

		// raise event w/o parameters
		$eventRaised = false;
		$this->component->on('test', function($event) use (&$eventRaised) {
			$eventRaised = true;
		});
		$this->component->trigger('test');
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

	public function testAttachBehavior()
	{
		$component = new NewComponent;
		$this->assertFalse($component->hasProperty('p'));
		$this->assertFalse($component->behaviorCalled);
		$this->assertNull($component->getBehavior('a'));

		$behavior = new NewBehavior;
		$component->attachBehavior('a', $behavior);
		$this->assertSame($behavior, $component->getBehavior('a'));
		$this->assertTrue($component->hasProperty('p'));
		$component->test();
		$this->assertTrue($component->behaviorCalled);

		$this->assertSame($behavior, $component->detachBehavior('a'));
		$this->assertFalse($component->hasProperty('p'));
		$this->setExpectedException('yii\base\UnknownMethodException');
		$component->test();

		$p = 'as b';
		$component = new NewComponent;
		$component->$p = array('class' => 'NewBehavior');
		$this->assertSame($behavior, $component->getBehavior('a'));
		$this->assertTrue($component->hasProperty('p'));
		$component->test();
		$this->assertTrue($component->behaviorCalled);
	}

	public function testAttachBehaviors()
	{
		$component = new NewComponent;
		$this->assertNull($component->getBehavior('a'));
		$this->assertNull($component->getBehavior('b'));

		$behavior = new NewBehavior;

		$component->attachBehaviors(array(
			'a' => $behavior,
			'b' => $behavior,
		));

		$this->assertSame(array('a' => $behavior, 'b' => $behavior), $component->getBehaviors());
	}

	public function testDetachBehavior()
	{
		$component = new NewComponent;
		$behavior = new NewBehavior;

		$component->attachBehavior('a', $behavior);
		$this->assertSame($behavior, $component->getBehavior('a'));

		$detachedBehavior = $component->detachBehavior('a');
		$this->assertSame($detachedBehavior, $behavior);
		$this->assertNull($component->getBehavior('a'));

		$detachedBehavior = $component->detachBehavior('z');
		$this->assertNull($detachedBehavior);
	}

	public function testDetachBehaviors()
	{
		$component = new NewComponent;
		$behavior = new NewBehavior;

		$component->attachBehavior('a', $behavior);
		$this->assertSame($behavior, $component->getBehavior('a'));
		$component->attachBehavior('b', $behavior);
		$this->assertSame($behavior, $component->getBehavior('b'));

		$component->detachBehaviors();
		$this->assertNull($component->getBehavior('a'));
		$this->assertNull($component->getBehavior('b'));

	}
}

class NewComponent extends Component
{
	private $_object = null;
	private $_text = 'default';
	private $_items = array();
	public $content;

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
			$this->_object = new self;
			$this->_object->_text = 'object text';
		}
		return $this->_object;
	}

	public function getExecute()
	{
		return function($param) {
			return $param * 2;
		};
	}

	public function getItems()
	{
		return $this->_items;
	}

	public $eventHandled = false;
	public $event;
	public $behaviorCalled = false;

	public function myEventHandler($event)
	{
		$this->eventHandled = true;
		$this->event = $event;
	}

	public function raiseEvent()
	{
		$this->trigger('click', new Event($this));
	}
}

class NewBehavior extends Behavior
{
	public $p;
	private $p2;

	public function getP2()
	{
		return $this->p2;
	}

	public function setP2($value)
	{
		$this->p2 = $value;
	}

	public function test()
	{
		$this->owner->behaviorCalled = true;
		return 2;
	}
}

class NewComponent2 extends Component
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