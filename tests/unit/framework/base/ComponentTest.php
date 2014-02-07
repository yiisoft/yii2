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

/**
 * @group base
 */
class ComponentTest extends TestCase
{
	/**
	 * @var NewComponent
	 */
	protected $component;

	protected function setUp()
	{
		parent::setUp();
		$this->mockApplication();
		$this->component = new NewComponent();
	}

	protected function tearDown()
	{
		parent::tearDown();
		$this->component = null;
	}

	public function testClone()
	{
		$behavior = new NewBehavior();
		$this->component->attachBehavior('a', $behavior);
		$this->assertSame($behavior, $this->component->getBehavior('a'));
		$this->component->on('test', 'fake');
		$this->assertTrue($this->component->hasEventHandlers('test'));

		$clone = clone $this->component;
		$this->assertNotSame($this->component, $clone);
		$this->assertNull($clone->getBehavior('a'));
		$this->assertFalse($clone->hasEventHandlers('test'));
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

	public function testCallUnknownMethod()
	{
		$this->setExpectedException('yii\base\UnknownMethodException');
		$this->component->unknownMethod();
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
		$this->assertFalse($this->component->hasEventHandlers('click'));
		$this->component->on('click', 'foo');
		$this->assertTrue($this->component->hasEventHandlers('click'));

		$this->assertFalse($this->component->hasEventHandlers('click2'));
		$p = 'on click2';
		$this->component->$p = 'foo2';
		$this->assertTrue($this->component->hasEventHandlers('click2'));
	}

	public function testOff()
	{
		$this->assertFalse($this->component->hasEventHandlers('click'));
		$this->component->on('click', 'foo');
		$this->assertTrue($this->component->hasEventHandlers('click'));
		$this->component->off('click', 'foo');
		$this->assertFalse($this->component->hasEventHandlers('click'));

		$this->component->on('click2', 'foo');
		$this->component->on('click2', 'foo2');
		$this->component->on('click2', 'foo3');
		$this->assertTrue($this->component->hasEventHandlers('click2'));
		$this->component->off('click2', 'foo3');
		$this->assertTrue($this->component->hasEventHandlers('click2'));
		$this->component->off('click2');
		$this->assertFalse($this->component->hasEventHandlers('click2'));
	}

	public function testTrigger()
	{
		$this->component->on('click', [$this->component, 'myEventHandler']);
		$this->assertFalse($this->component->eventHandled);
		$this->assertNull($this->component->event);
		$this->component->raiseEvent();
		$this->assertTrue($this->component->eventHandled);
		$this->assertEquals('click', $this->component->event->name);
		$this->assertEquals($this->component, $this->component->event->sender);
		$this->assertFalse($this->component->event->handled);

		$eventRaised = false;
		$this->component->on('click', function ($event) use (&$eventRaised) {
			$eventRaised = true;
		});
		$this->component->raiseEvent();
		$this->assertTrue($eventRaised);

		// raise event w/o parameters
		$eventRaised = false;
		$this->component->on('test', function ($event) use (&$eventRaised) {
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
		$component->on('click', [$this->component, 'myEventHandler']);
		$component->raiseEvent();
		$this->assertTrue($component->eventHandled);
		$this->assertFalse($this->component->eventHandled);
	}

	public function testAttachBehavior()
	{
		$this->assertFalse($this->component->hasProperty('p'));
		$this->assertFalse($this->component->behaviorCalled);
		$this->assertNull($this->component->getBehavior('a'));

		$behavior = new NewBehavior;
		$this->component->attachBehavior('a', $behavior);
		$this->assertSame($behavior, $this->component->getBehavior('a'));
		$this->assertTrue($this->component->hasProperty('p'));
		$this->component->test();
		$this->assertTrue($this->component->behaviorCalled);

		$this->assertSame($behavior, $this->component->detachBehavior('a'));
		$this->assertFalse($this->component->hasProperty('p'));
		$this->setExpectedException('yii\base\UnknownMethodException');
		$this->component->test();

		$p = 'as b';
		$component = new NewComponent;
		$component->$p = ['class' => 'NewBehavior'];
		$this->assertSame($behavior, $component->getBehavior('a'));
		$this->assertTrue($component->hasProperty('p'));
		$component->test();
		$this->assertTrue($component->behaviorCalled);
	}

	public function testAttachBehaviors()
	{
		$this->assertNull($this->component->getBehavior('a'));
		$this->assertNull($this->component->getBehavior('b'));

		$behavior = new NewBehavior;

		$this->component->attachBehaviors([
			'a' => $behavior,
			'b' => $behavior,
		]);

		$this->assertSame(['a' => $behavior, 'b' => $behavior], $this->component->getBehaviors());
	}

	public function testDetachBehavior()
	{
		$behavior = new NewBehavior;

		$this->component->attachBehavior('a', $behavior);
		$this->assertSame($behavior, $this->component->getBehavior('a'));

		$detachedBehavior = $this->component->detachBehavior('a');
		$this->assertSame($detachedBehavior, $behavior);
		$this->assertNull($this->component->getBehavior('a'));

		$detachedBehavior = $this->component->detachBehavior('z');
		$this->assertNull($detachedBehavior);
	}

	public function testDetachBehaviors()
	{
		$behavior = new NewBehavior;

		$this->component->attachBehavior('a', $behavior);
		$this->assertSame($behavior, $this->component->getBehavior('a'));
		$this->component->attachBehavior('b', $behavior);
		$this->assertSame($behavior, $this->component->getBehavior('b'));

		$this->component->detachBehaviors();
		$this->assertNull($this->component->getBehavior('a'));
		$this->assertNull($this->component->getBehavior('b'));
	}

	/**
	 * @expectedException \yii\base\UnknownPropertyException
	 * @expectedExceptionMessage Setting unknown property: yiiunit\framework\base\NewComponent::invalidProperty
	 */
	public function testSetInvalidProperty()
	{
		$this->component->invalidProperty = 3;
	}

	public function testSuccessfulMethodCheck()
	{
		$this->assertTrue($this->component->hasMethod('hasProperty'));
	}

	/**
	 * @expectedException \yii\base\InvalidCallException
	 * @expectedExceptionMessage Getting write-only property: yiiunit\framework\base\NewComponent::writeOnly
	 */
	public function testWriteOnlyProperty()
	{
		$this->component->writeOnly;
	}

	public function testWorkingUnset()
	{
		$this->assertSame('default', $this->component->getText());
		unset($this->component->text);
		$this->assertNull($this->component->getText());
	}

	public function testTurningOffNonExistingBehavior()
	{
		$this->assertFalse($this->component->hasEventHandlers('foo'));
		$this->assertFalse($this->component->off('foo'));
	}

	public function testSettingBehaviorWithSetter()
	{
		$behaviorName = 'foo';
		$this->assertNull($this->component->getBehavior($behaviorName));
		$p = 'as ' . $behaviorName;
		$this->component->$p = __NAMESPACE__ .  '\NewBehavior';
		$this->assertSame(__NAMESPACE__ .  '\NewBehavior', get_class($this->component->getBehavior($behaviorName)));
	}

	public function testSetPropertyOfBehavior()
	{
		$this->assertNull($this->component->getBehavior('a'));

		$behavior = new NewBehavior;
		$this->component->attachBehaviors([
			'a' => $behavior,
		]);
		$this->component->p = 'Yii is cool.';

		$this->assertSame('Yii is cool.', $this->component->getBehavior('a')->p);
	}

	/**
	 * @expectedException \yii\base\InvalidCallException
	 * @expectedExceptionMessage Setting read-only property: yiiunit\framework\base\NewComponent::object
	 */
	public function testSetReadOnlyProperty()
	{
		$this->component->object = 'z';
	}

	/**
	 * @expectedException \yii\base\UnknownPropertyException
	 * @expectedExceptionMessage Setting unknown property: yiiunit\framework\base\NewComponent::invalidProperty
	 */
	public function testSetInvalidProperty()
	{
		$component = new NewComponent();
		$component->invalidProperty = 3;
	}

	public function testSuccessfulMethodCheck()
	{
		$component = new NewComponent();
		$this->assertTrue($component->hasMethod('hasProperty'));
	}

	/**
	 * @expectedException \yii\base\InvalidCallException
	 * @expectedExceptionMessage Getting write-only property: yiiunit\framework\base\NewComponent::writeOnly
	 */
	public function testWriteOnlyProperty()
	{
		$component = new NewComponent();
		$component->writeOnly;
	}

	public function testWorkingUnset()
	{
		$component = new NewComponent();
		$component->content = 'foo';
		$component->__unset('content');
		var_dump($component);die;
		// $this->assertNull($component->content);
	}

//	public function testTurningOffNonExistingBehavior()
//	{
//		$component = new NewComponent();
//		$this->assertFalse($component->off('foo'));
//	}
}

class NewComponent extends Component
{
	private $_object = null;
	private $_text = 'default';
	private $_items = [];
	private $_writeOnly;
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
		return function ($param) {
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
		$this->trigger('click', new Event);
	}

	public function setWriteOnly($value)
	{
		$this->_writeOnly = $value;
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

