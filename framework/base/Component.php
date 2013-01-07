<?php
/**
 * Component class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * Component is the base class that provides the *property*, *event* and *behavior* features.
 *
 * @include @yii/docs/base-Component.md
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Component extends \yii\base\Object
{
	/**
	 * @var Vector[] the attached event handlers (event name => handlers)
	 */
	private $_e;
	/**
	 * @var Behavior[] the attached behaviors (behavior name => behavior)
	 */
	private $_b;

	/**
	 * Returns the value of a component property.
	 * This method will check in the following order and act accordingly:
	 *
	 *  - a property defined by a getter: return the getter result
	 *  - a property of a behavior: return the behavior property value
	 *
	 * Do not call this method directly as it is a PHP magic method that
	 * will be implicitly called when executing `$value = $component->property;`.
	 * @param string $name the property name
	 * @return mixed the property value, event handlers attached to the event,
	 * the named behavior, or the value of a behavior's property
	 * @throws BadPropertyException if the property is not defined
	 * @see __set
	 */
	public function __get($name)
	{
		$getter = 'get' . $name;
		if (method_exists($this, $getter)) {
			// read property, e.g. getName()
			return $this->$getter();
		} else {
			// behavior property
			$this->ensureBehaviors();
			foreach ($this->_b as $i => $behavior) {
				if (is_string($i) && $behavior->canGetProperty($name)) {
					return $behavior->$name;
				}
			}
		}
		throw new BadPropertyException('Getting unknown property: ' . get_class($this) . '.' . $name);
	}

	/**
	 * Sets the value of a component property.
	 * This method will check in the following order and act accordingly:
	 *
	 *  - a property defined by a setter: set the property value
	 *  - an event in the format of "on xyz": attach the handler to the event "xyz"
	 *  - a behavior in the format of "as xyz": attach the behavior named as "xyz"
	 *  - a property of a behavior: set the behavior property value
	 *
	 * Do not call this method directly as it is a PHP magic method that
	 * will be implicitly called when executing `$component->property = $value;`.
	 * @param string $name the property name or the event name
	 * @param mixed $value the property value
	 * @throws BadPropertyException if the property is not defined or read-only.
	 * @see __get
	 */
	public function __set($name, $value)
	{
		$setter = 'set' . $name;
		if (method_exists($this, $setter)) {
			// set property
			$this->$setter($value);
			return;
		} elseif (strncmp($name, 'on ', 3) === 0) {
			// on event: attach event handler
			$name = trim(substr($name, 3));
			$this->getEventHandlers($name)->add($value);
			return;
		} elseif (strncmp($name, 'as ', 3) === 0) {
			// as behavior: attach behavior
			$name = trim(substr($name, 3));
			$this->attachBehavior($name, $value instanceof Behavior ? $value : \Yii::createObject($value));
		} else {
			// behavior property
			$this->ensureBehaviors();
			foreach ($this->_b as $i => $behavior) {
				if (is_string($i) && $behavior->canSetProperty($name)) {
					$behavior->$name = $value;
					return;
				}
			}
		}
		if (method_exists($this, 'get' . $name)) {
			throw new BadPropertyException('Setting read-only property: ' . get_class($this) . '.' . $name);
		} else {
			throw new BadPropertyException('Setting unknown property: ' . get_class($this) . '.' . $name);
		}
	}

	/**
	 * Checks if a property value is null.
	 * This method will check in the following order and act accordingly:
	 *
	 *  - a property defined by a setter: return whether the property value is null
	 *  - a property of a behavior: return whether the property value is null
	 *
	 * Do not call this method directly as it is a PHP magic method that
	 * will be implicitly called when executing `isset($component->property)`.
	 * @param string $name the property name or the event name
	 * @return boolean whether the named property is null
	 */
	public function __isset($name)
	{
		$getter = 'get' . $name;
		if (method_exists($this, $getter)) {
			// property is not null
			return $this->$getter() !== null;
		} else {
			// behavior property
			$this->ensureBehaviors();
			foreach ($this->_b as $i => $behavior) {
				if (is_string($i) && $behavior->canGetProperty($name)) {
					return $behavior->$name !== null;
				}
			}
		}
		return false;
	}

	/**
	 * Sets a component property to be null.
	 * This method will check in the following order and act accordingly:
	 *
	 *  - a property defined by a setter: set the property value to be null
	 *  - a property of a behavior: set the property value to be null
	 *
	 * Do not call this method directly as it is a PHP magic method that
	 * will be implicitly called when executing `unset($component->property)`.
	 * @param string $name the property name
	 * @throws BadPropertyException if the property is read only.
	 */
	public function __unset($name)
	{
		$setter = 'set' . $name;
		if (method_exists($this, $setter)) {
			// write property
			$this->$setter(null);
			return;
		} else {
			// behavior property
			$this->ensureBehaviors();
			foreach ($this->_b as $i => $behavior) {
				if (is_string($i) && $behavior->canSetProperty($name)) {
					$behavior->$name = null;
					return;
				}
			}
		}
		if (method_exists($this, 'get' . $name)) {
			throw new BadPropertyException('Unsetting read-only property: ' . get_class($this) . '.' . $name);
		}
	}

	/**
	 * Calls the named method which is not a class method.
	 * If the name refers to a component property whose value is
	 * an anonymous function, the method will execute the function.
	 * Otherwise, it will check if any attached behavior has
	 * the named method and will execute it if available.
	 *
	 * Do not call this method directly as it is a PHP magic method that
	 * will be implicitly called when an unknown method is being invoked.
	 * @param string $name the method name
	 * @param array $params method parameters
	 * @return mixed the method return value
	 * @throws BadMethodException when calling unknown method
	 */
	public function __call($name, $params)
	{
		if ($this->canGetProperty($name, false)) {
			$func = $this->$name;
			if ($func instanceof \Closure) {
				return call_user_func_array($func, $params);
			}
		}

		$this->ensureBehaviors();
		foreach ($this->_b as $i => $object) {
			if (is_string($i) && method_exists($object, $name)) {
				return call_user_func_array(array($object, $name), $params);
			}
		}

		throw new BadMethodException('Calling unknown method: ' . get_class($this) . "::$name()");
	}

	/**
	 * This method is called after the object is created by cloning an existing one.
	 * It removes all behaviors because they are attached to the old object.
	 */
	public function __clone()
	{
		$this->_b = null;
	}

	/**
	 * Returns a list of behaviors that this component should behave as.
	 *
	 * Child classes may override this method to specify the behaviors they want to behave as.
	 *
	 * The return value of this method should be an array of behavior objects or configurations
	 * indexed by behavior names. A behavior configuration can be either a string specifying
	 * the behavior class or an array of the following structure:
	 *
	 * ~~~
	 * 'behaviorName' => array(
	 *     'class' => 'BehaviorClass',
	 *     'property1' => 'value1',
	 *     'property2' => 'value2',
	 * )
	 * ~~~
	 *
	 * Note that a behavior class must extend from [[Behavior]]. Behavior names can be strings
	 * or integers. If the former, they uniquely identify the behaviors. If the latter, the corresponding
	 * behaviors are anonymous and their properties and methods will NOT be made available via the component
	 * (however, the behaviors can still respond to the component's events).
	 *
	 * Behaviors declared in this method will be attached to the component automatically (on demand).
	 *
	 * @return array the behavior configurations.
	 */
	public function behaviors()
	{
		return array();
	}

	/**
	 * Returns a value indicating whether there is any handler attached to the named event.
	 * @param string $name the event name
	 * @return boolean whether there is any handler attached to the event.
	 */
	public function hasEventHandlers($name)
	{
		$this->ensureBehaviors();
		return isset($this->_e[$name]) && $this->_e[$name]->getCount();
	}

	/**
	 * Returns the list of attached event handlers for an event.
	 * You may manipulate the returned [[Vector]] object by adding or removing handlers.
	 * For example,
	 *
	 * ~~~
	 * $component->getEventHandlers($eventName)->insertAt(0, $eventHandler);
	 * ~~~
	 *
	 * @param string $name the event name
	 * @return Vector list of attached event handlers for the event
	 * @throws Exception if the event is not defined
	 */
	public function getEventHandlers($name)
	{
		if (!isset($this->_e[$name])) {
			$this->_e[$name] = new Vector;
		}
		$this->ensureBehaviors();
		return $this->_e[$name];
	}

	/**
	 * Attaches an event handler to an event.
	 *
	 * This is equivalent to the following code:
	 *
	 * ~~~
	 * $component->getEventHandlers($eventName)->add($eventHandler);
	 * ~~~
	 *
	 * An event handler must be a valid PHP callback. The followings are
	 * some examples:
	 *
	 * ~~~
	 * function($event) { ... }         // anonymous function
	 * array($object, 'handleClick')    // $object->handleClick()
	 * array('Page', 'handleClick')     // Page::handleClick()
	 * 'handleClick'                    // global function handleClick()
	 * ~~~
	 *
	 * An event handler must be defined with the following signature,
	 *
	 * ~~~
	 * function handlerName($event) {}
	 * ~~~
	 *
	 * where `$event` is an [[Event]] object which includes parameters associated with the event.
	 *
	 * @param string $name the event name
	 * @param string|array|\Closure $handler the event handler
	 * @see off
	 */
	public function on($name, $handler)
	{
		$this->getEventHandlers($name)->add($handler);
	}

	/**
	 * Detaches an existing event handler.
	 * This method is the opposite of [[on]].
	 * @param string $name event name
	 * @param string|array|\Closure $handler the event handler to be removed
	 * @return boolean if a handler is found and detached
	 * @see on
	 */
	public function off($name, $handler)
	{
		return $this->getEventHandlers($name)->remove($handler) !== false;
	}

	/**
	 * Triggers an event.
	 * This method represents the happening of an event. It invokes
	 * all attached handlers for the event.
	 * @param string $name the event name
	 * @param Event $event the event parameter. If not set, a default [[Event]] object will be created.
	 * @throws Exception if the event is undefined or an event handler is invalid.
	 */
	public function trigger($name, $event = null)
	{
		$this->ensureBehaviors();
		if (isset($this->_e[$name])) {
			if ($event === null) {
				$event = new Event($this);
			}
			if ($event instanceof Event) {
				$event->handled = false;
				$event->name = $name;
			}
			foreach ($this->_e[$name] as $handler) {
				call_user_func($handler, $event);
				// stop further handling if the event is handled
				if ($event instanceof Event && $event->handled) {
					return;
				}
			}
		}
	}

	/**
	 * Returns the named behavior object.
	 * @param string $name the behavior name
	 * @return Behavior the behavior object, or null if the behavior does not exist
	 */
	public function getBehavior($name)
	{
		$this->ensureBehaviors();
		return isset($this->_b[$name]) ? $this->_b[$name] : null;
	}

	/**
	 * Returns all behaviors attached to this component.
	 * @return Behavior[] list of behaviors attached to this component
	 */
	public function getBehaviors()
	{
		$this->ensureBehaviors();
		return $this->_b;
	}

	/**
	 * Attaches a behavior to this component.
	 * This method will create the behavior object based on the given
	 * configuration. After that, the behavior object will be attached to
	 * this component by calling the [[Behavior::attach]] method.
	 * @param integer|string $name the name of the behavior. This can be a string or an integer (or empty string).
	 * If the former, it uniquely identifies this behavior. If the latter, the behavior becomes
	 * anonymous and its methods and properties will NOT be made available in this component.
	 * @param string|array|Behavior $behavior the behavior configuration. This can be one of the following:
	 *
	 *  - a [[Behavior]] object
	 *  - a string specifying the behavior class
	 *  - an object configuration array that will be passed to [[\Yii::createObject()]] to create the behavior object.
	 *
	 * @return Behavior the behavior object
	 * @see detachBehavior
	 */
	public function attachBehavior($name, $behavior)
	{
		$this->ensureBehaviors();
		return $this->attachBehaviorInternal($name, $behavior);
	}

	/**
	 * Attaches a list of behaviors to the component.
	 * Each behavior is indexed by its name and should be a [[Behavior]] object,
	 * a string specifying the behavior class, or an configuration array for creating the behavior.
	 * @param array $behaviors list of behaviors to be attached to the component
	 * @see attachBehavior
	 */
	public function attachBehaviors($behaviors)
	{
		$this->ensureBehaviors();
		foreach ($behaviors as $name => $behavior) {
			$this->attachBehaviorInternal($name, $behavior);
		}
	}

	/**
	 * Detaches a behavior from the component.
	 * The behavior's [[Behavior::detach]] method will be invoked.
	 * @param string $name the behavior's name.
	 * @return Behavior the detached behavior. Null if the behavior does not exist.
	 */
	public function detachBehavior($name)
	{
		$this->ensureBehaviors();
		if (isset($this->_b[$name])) {
			$behavior = $this->_b[$name];
			unset($this->_b[$name]);
			$behavior->detach($this);
			return $behavior;
		} else {
			return null;
		}
	}

	/**
	 * Detaches all behaviors from the component.
	 */
	public function detachBehaviors()
	{
		if ($this->_b !== null) {
			foreach ($this->_b as $name => $behavior) {
				$this->detachBehavior($name);
			}
		}
		$this->_b = array();
	}

	/**
	 * Makes sure that the behaviors declared in [[behaviors()]] are attached to this component.
	 */
	public function ensureBehaviors()
	{
		if ($this->_b === null) {
			$this->_b = array();
			foreach ($this->behaviors() as $name => $behavior) {
				$this->attachBehaviorInternal($name, $behavior);
			}
		}
	}

	/**
	 * Attaches a behavior to this component.
	 * @param integer|string $name the name of the behavior. If it is an integer or an empty string,
	 * the behavior is anonymous and its methods and properties will NOT be made available to the owner component.
	 * @param string|array|Behavior $behavior the behavior to be attached
	 * @return Behavior the attached behavior.
	 */
	private function attachBehaviorInternal($name, $behavior)
	{
		if (!($behavior instanceof Behavior)) {
			$behavior = \Yii::createObject($behavior);
		}
		if (is_int($name) || $name == '') {
			// anonymous behavior
			$behavior->attach($this);
			return $this->_b[] = $behavior;
		} else {
			if (isset($this->_b[$name])) {
				$this->_b[$name]->detach($this);
			}
			$behavior->attach($this);
			return $this->_b[$name] = $behavior;
		}
	}
}
