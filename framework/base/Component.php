<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;

/**
 * @include @yii/base/Component.md
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Component extends Object
{
	/**
	 * @var array the attached event handlers (event name => handlers)
	 */
	private $_events;
	/**
	 * @var Behavior[] the attached behaviors (behavior name => behavior)
	 */
	private $_behaviors;

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
	 * the behavior, or the value of a behavior's property
	 * @throws UnknownPropertyException if the property is not defined
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
			foreach ($this->_behaviors as $behavior) {
				if ($behavior->canGetProperty($name)) {
					return $behavior->$name;
				}
			}
		}
		throw new UnknownPropertyException('Getting unknown property: ' . get_class($this) . '::' . $name);
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
	 * @throws UnknownPropertyException if the property is not defined
	 * @throws InvalidCallException if the property is read-only.
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
			$this->on(trim(substr($name, 3)), $value);
			return;
		} elseif (strncmp($name, 'as ', 3) === 0) {
			// as behavior: attach behavior
			$name = trim(substr($name, 3));
			$this->attachBehavior($name, $value instanceof Behavior ? $value : Yii::createObject($value));
			return;
		} else {
			// behavior property
			$this->ensureBehaviors();
			foreach ($this->_behaviors as $behavior) {
				if ($behavior->canSetProperty($name)) {
					$behavior->$name = $value;
					return;
				}
			}
		}
		if (method_exists($this, 'get' . $name)) {
			throw new InvalidCallException('Setting read-only property: ' . get_class($this) . '::' . $name);
		} else {
			throw new UnknownPropertyException('Setting unknown property: ' . get_class($this) . '::' . $name);
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
			return $this->$getter() !== null;
		} else {
			// behavior property
			$this->ensureBehaviors();
			foreach ($this->_behaviors as $behavior) {
				if ($behavior->canGetProperty($name)) {
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
	 * @throws InvalidCallException if the property is read only.
	 */
	public function __unset($name)
	{
		$setter = 'set' . $name;
		if (method_exists($this, $setter)) {
			$this->$setter(null);
			return;
		} else {
			// behavior property
			$this->ensureBehaviors();
			foreach ($this->_behaviors as $behavior) {
				if ($behavior->canSetProperty($name)) {
					$behavior->$name = null;
					return;
				}
			}
		}
		if (method_exists($this, 'get' . $name)) {
			throw new InvalidCallException('Unsetting read-only property: ' . get_class($this) . '.' . $name);
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
	 * @throws UnknownMethodException when calling unknown method
	 */
	public function __call($name, $params)
	{
		$getter = 'get' . $name;
		if (method_exists($this, $getter)) {
			$func = $this->$getter();
			if ($func instanceof \Closure) {
				return call_user_func_array($func, $params);
			}
		}

		$this->ensureBehaviors();
		foreach ($this->_behaviors as $object) {
			if (method_exists($object, $name)) {
				return call_user_func_array(array($object, $name), $params);
			}
		}

		throw new UnknownMethodException('Calling unknown method: ' . get_class($this) . "::$name()");
	}

	/**
	 * This method is called after the object is created by cloning an existing one.
	 * It removes all behaviors because they are attached to the old object.
	 */
	public function __clone()
	{
		$this->_events = null;
		$this->_behaviors = null;
	}

	/**
	 * Returns a value indicating whether a property is defined for this component.
	 * A property is defined if:
	 *
	 * - the class has a getter or setter method associated with the specified name
	 *   (in this case, property name is case-insensitive);
	 * - the class has a member variable with the specified name (when `$checkVar` is true);
	 * - an attached behavior has a property of the given name (when `$checkBehavior` is true).
	 *
	 * @param string $name the property name
	 * @param boolean $checkVar whether to treat member variables as properties
	 * @param boolean $checkBehavior whether to treat behaviors' properties as properties of this component
	 * @return boolean whether the property is defined
	 * @see canGetProperty
	 * @see canSetProperty
	 */
	public function hasProperty($name, $checkVar = true, $checkBehavior = true)
	{
		return $this->canGetProperty($name, $checkVar, $checkBehavior) || $this->canSetProperty($name, $checkVar, $checkBehavior);
	}

	/**
	 * Returns a value indicating whether a property can be read.
	 * A property can be read if:
	 *
	 * - the class has a getter method associated with the specified name
	 *   (in this case, property name is case-insensitive);
	 * - the class has a member variable with the specified name (when `$checkVar` is true);
	 * - an attached behavior has a readable property of the given name (when `$checkBehavior` is true).
	 *
	 * @param string $name the property name
	 * @param boolean $checkVar whether to treat member variables as properties
	 * @param boolean $checkBehavior whether to treat behaviors' properties as properties of this component
	 * @return boolean whether the property can be read
	 * @see canSetProperty
	 */
	public function canGetProperty($name, $checkVar = true, $checkBehavior = true)
	{
		if (method_exists($this, 'get' . $name) || $checkVar && property_exists($this, $name)) {
			return true;
		} else {
			$this->ensureBehaviors();
			foreach ($this->_behaviors as $behavior) {
				if ($behavior->canGetProperty($name, $checkVar)) {
					return true;
				}
			}
			return false;
		}
	}

	/**
	 * Returns a value indicating whether a property can be set.
	 * A property can be written if:
	 *
	 * - the class has a setter method associated with the specified name
	 *   (in this case, property name is case-insensitive);
	 * - the class has a member variable with the specified name (when `$checkVar` is true);
	 * - an attached behavior has a writable property of the given name (when `$checkBehavior` is true).
	 *
	 * @param string $name the property name
	 * @param boolean $checkVar whether to treat member variables as properties
	 * @param boolean $checkBehavior whether to treat behaviors' properties as properties of this component
	 * @return boolean whether the property can be written
	 * @see canGetProperty
	 */
	public function canSetProperty($name, $checkVar = true, $checkBehavior = true)
	{
		if (method_exists($this, 'set' . $name) || $checkVar && property_exists($this, $name)) {
			return true;
		} else {
			$this->ensureBehaviors();
			foreach ($this->_behaviors as $behavior) {
				if ($behavior->canSetProperty($name, $checkVar)) {
					return true;
				}
			}
			return false;
		}
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
		return !empty($this->_events[$name]);
	}

	/**
	 * Attaches an event handler to an event.
	 *
	 * An event handler must be a valid PHP callback. The followings are
	 * some examples:
	 *
	 * ~~~
	 * function ($event) { ... }         // anonymous function
	 * array($object, 'handleClick')    // $object->handleClick()
	 * array('Page', 'handleClick')     // Page::handleClick()
	 * 'handleClick'                    // global function handleClick()
	 * ~~~
	 *
	 * An event handler must be defined with the following signature,
	 *
	 * ~~~
	 * function ($event)
	 * ~~~
	 *
	 * where `$event` is an [[Event]] object which includes parameters associated with the event.
	 *
	 * @param string $name the event name
	 * @param callback $handler the event handler
	 * @param mixed $data the data to be passed to the event handler when the event is triggered.
	 * When the event handler is invoked, this data can be accessed via [[Event::data]].
	 * @see off()
	 */
	public function on($name, $handler, $data = null)
	{
		$this->ensureBehaviors();
		$this->_events[$name][] = array($handler, $data);
	}

	/**
	 * Detaches an existing event handler from this component.
	 * This method is the opposite of [[on()]].
	 * @param string $name event name
	 * @param callback $handler the event handler to be removed.
	 * If it is null, all handlers attached to the named event will be removed.
	 * @return boolean if a handler is found and detached
	 * @see on()
	 */
	public function off($name, $handler = null)
	{
		$this->ensureBehaviors();
		if (isset($this->_events[$name])) {
			if ($handler === null) {
				$this->_events[$name] = array();
			} else {
				$removed = false;
				foreach ($this->_events[$name] as $i => $event) {
					if ($event[0] === $handler) {
						unset($this->_events[$name][$i]);
						$removed = true;
					}
				}
				if ($removed) {
					$this->_events[$name] = array_values($this->_events[$name]);
				}
				return $removed;
			}
		}
		return false;
	}

	/**
	 * Triggers an event.
	 * This method represents the happening of an event. It invokes
	 * all attached handlers for the event.
	 * @param string $name the event name
	 * @param Event $event the event parameter. If not set, a default [[Event]] object will be created.
	 */
	public function trigger($name, $event = null)
	{
		$this->ensureBehaviors();
		if (!empty($this->_events[$name])) {
			if ($event === null) {
				$event = new Event;
			}
			if ($event->sender === null) {
				$event->sender = $this;
			}
			$event->handled = false;
			$event->name = $name;
			foreach ($this->_events[$name] as $handler) {
				$event->data = $handler[1];
				call_user_func($handler[0], $event);
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
		return isset($this->_behaviors[$name]) ? $this->_behaviors[$name] : null;
	}

	/**
	 * Returns all behaviors attached to this component.
	 * @return Behavior[] list of behaviors attached to this component
	 */
	public function getBehaviors()
	{
		$this->ensureBehaviors();
		return $this->_behaviors;
	}

	/**
	 * Attaches a behavior to this component.
	 * This method will create the behavior object based on the given
	 * configuration. After that, the behavior object will be attached to
	 * this component by calling the [[Behavior::attach()]] method.
	 * @param string $name the name of the behavior.
	 * @param string|array|Behavior $behavior the behavior configuration. This can be one of the following:
	 *
	 *  - a [[Behavior]] object
	 *  - a string specifying the behavior class
	 *  - an object configuration array that will be passed to [[Yii::createObject()]] to create the behavior object.
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
	 * The behavior's [[Behavior::detach()]] method will be invoked.
	 * @param string $name the behavior's name.
	 * @return Behavior the detached behavior. Null if the behavior does not exist.
	 */
	public function detachBehavior($name)
	{
		$this->ensureBehaviors();
		if (isset($this->_behaviors[$name])) {
			$behavior = $this->_behaviors[$name];
			unset($this->_behaviors[$name]);
			$behavior->detach();
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
		$this->ensureBehaviors();
		if ($this->_behaviors !== null) {
			foreach ($this->_behaviors as $name => $behavior) {
				$this->detachBehavior($name);
			}
		}
		$this->_behaviors = array();
	}

	/**
	 * Makes sure that the behaviors declared in [[behaviors()]] are attached to this component.
	 */
	public function ensureBehaviors()
	{
		if ($this->_behaviors === null) {
			$this->_behaviors = array();
			foreach ($this->behaviors() as $name => $behavior) {
				$this->attachBehaviorInternal($name, $behavior);
			}
		}
	}

	/**
	 * Attaches a behavior to this component.
	 * @param string $name the name of the behavior.
	 * @param string|array|Behavior $behavior the behavior to be attached
	 * @return Behavior the attached behavior.
	 */
	private function attachBehaviorInternal($name, $behavior)
	{
		if (!($behavior instanceof Behavior)) {
			$behavior = Yii::createObject($behavior);
		}
		if (isset($this->_behaviors[$name])) {
			$this->_behaviors[$name]->detach();
		}
		$behavior->attach($this);
		return $this->_behaviors[$name] = $behavior;
	}
}
