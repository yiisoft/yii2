<?php
/**
 * Component class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * Component is the base class for all component classes in Yii.
 *
 * Extending from [[Object]], Component implements the *event* and *behavior*
 * features in addition to the *property* feature.
 *
 * An event is defined by the presence of a method whose name starts with `on`.
 * The event name is the method name. For example, the following method defines
 * the `onClick` event:
 *
 * ~~~
 * public function onClick($event)
 * {
 *	 $this->raiseEvent('onClick', $event);
 * }
 * ~~~
 *
 * Event names are case-insensitive.
 *
 * An event can be attached with one or multiple PHP callbacks, called *event handlers*.
 * One can call [[raiseEvent]] to raise an event. When an event is raised, the attached
 * event handlers will be invoked automatically in the order they are attached to the event.
 *
 * To attach an event handler to an event, call [[attachEventHandler]]. Alternatively,
 * you can use the assignment syntax: `$component->onClick = $callback;`,
 * where `$callback` refers to a valid PHP callback, which can be one of the followings:
 *
 * - global function: `'handleOnClick'`
 * - object method: `array($object, 'handleOnClick')`
 * - static method: `array('Page', 'handleOnClick')`
 * - anonymous function: `function($event) { ... }`
 *
 * The signature of an event handler should be like the following:
 * ~~~
 * function foo($event)
 * ~~~
 *
 * where `$event` is an [[Event]] object which includes parameters associated with the event.
 *
 * Because `$component->onClick` is returned as a [[Vector]] with each item in the vector being
 * an attached event handler, one can manipulate this [[Vector]] object to attach/detach event
 * handlers, or adjust their relative orders. For example,
 *
 * ~~~
 * $component->onClick->insertAt(0, $callback);  // attach a handler as the first one
 * $component->onClick[] = $callback;			// attach a handler as the last one
 * unset($component->onClick[0]);				// detach the first handler
 * ~~~
 *
 *
 * A behavior is an instance of [[Behavior]] or its child class. When a behavior is
 * attached to a component, its public properties and methods can be accessed via the
 * component directly, as if the component owns those properties and methods.
 *
 * Multiple behaviors can be attached to the same component.
 *
 * To attach a behavior to a component, call [[attachBehavior]]; to detach a behavior
 * from the component, call [[detachBehavior]].
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Component extends Object
{
	private $_e;
	private $_b;

	/**
	 * Returns the value of a component property.
	 * This method will check in the following order and act accordingly:
	 *
	 *  - a property defined by a getter: return the getter result
	 *  - an event: return a vector containing the attached event handlers
	 *  - a behavior: return the behavior object
	 *  - a property of a behavior: return the behavior property value
	 *
	 * Do not call this method directly as it is a PHP magic method that
	 * will be implicitly called when executing `$value = $component->property;`.
	 * @param string $name the property name
	 * @return mixed the property value, event handlers attached to the event,
	 * the named behavior, or the value of a behavior's property
	 * @throws Exception if the property is not defined
	 * @see __set
	 */
	public function __get($name)
	{
		$getter = 'get' . $name;
		if (method_exists($this, $getter)) { // read property, e.g. getName()
			return $this->$getter();
		} elseif (method_exists($this, $name) && strncasecmp($name, 'on', 2) === 0) { // event, e.g. onClick()
			$name = strtolower($name);
			if (!isset($this->_e[$name])) {
				$this->_e[$name] = new Vector;
			}
			return $this->_e[$name];
		} elseif (isset($this->_b[$name])) { // behavior
			return $this->_b[$name];
		} elseif (is_array($this->_b)) { // a behavior property
			foreach ($this->_b as $object) {
				if ($object->canGetProperty($name)) {
					return $object->$name;
				}
			}
		}
		throw new Exception('Getting unknown property: ' . get_class($this) . '.' . $name);
	}

	/**
	 * Sets the value of a component property.
	 * This method will check in the following order and act accordingly:
	 *
	 *  - a property defined by a setter: set the property value
	 *  - an event: attach the handler to the event
	 *  - a property of a behavior: set the behavior property value
	 *
	 * Do not call this method directly as it is a PHP magic method that
	 * will be implicitly called when executing `$component->property = $value;`.
	 * @param string $name the property name or the event name
	 * @param mixed $value the property value
	 * @return mixed value that was set
	 * @throws Exception if the property is not defined or read-only.
	 * @see __get
	 */
	public function __set($name, $value)
	{
		$setter = 'set' . $name;
		if (method_exists($this, $setter)) { // write property
			return $this->$setter($value);
		} elseif (method_exists($this, $name) && strncasecmp($name, 'on', 2) === 0) { // event
			$name = strtolower($name);
			if (!isset($this->_e[$name])) {
				$this->_e[$name] = new Vector;
			}
			return $this->_e[$name]->add($value);
		} elseif (is_array($this->_b)) { // behavior
			foreach ($this->_b as $object) {
				if ($object->canSetProperty($name)) {
					return $object->$name = $value;
				}
			}
		}
		if (method_exists($this, 'get' . $name)) {
			throw new Exception('Setting read-only property: ' . get_class($this) . '.' . $name);
		} else {
			throw new Exception('Setting unknown property: ' . get_class($this) . '.' . $name);
		}
	}

	/**
	 * Checks if a property value is null.
	 * This method will check in the following order and act accordingly:
	 *
	 *  - a property defined by a setter: return whether the property value is null
	 *  - an event: return whether the event has any handler attached
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
		if (method_exists($this, $getter)) { // property is not null
			return $this->$getter() !== null;
		} elseif (method_exists($this, $name) && strncasecmp($name, 'on', 2) === 0) { // has event handler
			$name = strtolower($name);
			return isset($this->_e[$name]) && $this->_e[$name]->getCount();
		} elseif (isset($this->_b[$name])) { // has behavior
			return true;
		} elseif (is_array($this->_b)) {
			foreach ($this->_b as $object) {
				if ($object->canGetProperty($name)) {
					return $object->$name !== null;
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
	 *  - an event: remove all attached event handlers
	 *  - a property of a behavior: set the property value to be null
	 *
	 * Do not call this method directly as it is a PHP magic method that
	 * will be implicitly called when executing `unset($component->property)`.
	 * @param string $name the property name
	 * @throws Exception if the property is read only.
	 */
	public function __unset($name)
	{
		$setter = 'set' . $name;
		if (method_exists($this, $setter)) { // write property
			return $this->$setter(null);
		} elseif (method_exists($this, $name) && strncasecmp($name, 'on', 2) === 0) { // event
			unset($this->_e[strtolower($name)]);
			return;
		} elseif (isset($this->_b[$name])) { // behavior
			return $this->detachBehavior($name);
		} elseif (is_array($this->_b)) { // behavior property
			foreach ($this->_b as $object) {
				if ($object->canSetProperty($name)) {
					return $object->$name = null;
				}
			}
		}
		if (method_exists($this, 'get' . $name)) {
			throw new Exception('Unsetting read-only property: ' . get_class($this) . '.' . $name);
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
	 */
	public function __call($name, $params)
	{
		if ($this->canGetProperty($name, false)) {
			$func = $this->$name;
			if ($func instanceof \Closure) {
				return call_user_func_array($func, $params);
			}
		}

		if ($this->_b !== null) {
			foreach ($this->_b as $object) {
				if (method_exists($object, $name)) {
					return call_user_func_array(array($object, $name), $params);
				}
			}
		}
		throw new Exception('Unknown method: ' . get_class($this) . "::$name()");
	}

	/**
	 * Returns a value indicating whether an event is defined.
	 * An event is defined if the class has a method whose name starts with `on` (e.g. `onClick`).
	 * Note that event name is case-insensitive.
	 * @param string $name the event name
	 * @return boolean whether an event is defined
	 */
	public function hasEvent($name)
	{
		return method_exists($this, $name) && strncasecmp($name, 'on', 2) === 0;
	}

	/**
	 * Returns a value indicating whether there is any handler attached to the event.
	 * @param string $name the event name
	 * @return boolean whether there is any handler attached to the event.
	 */
	public function hasEventHandlers($name)
	{
		$name = strtolower($name);
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
		if ($this->hasEvent($name)) {
			$name = strtolower($name);
			if (!isset($this->_e[$name])) {
				$this->_e[$name] = new Vector;
			}
			return $this->_e[$name];
		}
		throw new Exception('Undefined event: ' . $name);
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
	 * 'handleOnClick'					// handleOnClick() is a global function
	 * array($object, 'handleOnClick')	// $object->handleOnClick()
	 * array('Page', 'handleOnClick')	 // Page::handleOnClick()
	 * function($event) { ... }		   // anonymous function
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
	 * @param callback $handler the event handler
	 * @throws Exception if the event is not defined
	 * @see detachEventHandler
	 */
	public function attachEventHandler($name, $handler)
	{
		$this->getEventHandlers($name)->add($handler);
	}

	/**
	 * Detaches an existing event handler.
	 * This method is the opposite of [[attachEventHandler]].
	 * @param string $name event name
	 * @param callback $handler the event handler to be removed
	 * @return boolean if the detachment process is successful
	 * @see attachEventHandler
	 */
	public function detachEventHandler($name, $handler)
	{
		return $this->getEventHandlers($name)->remove($handler) !== false;
	}

	/**
	 * Raises an event.
	 * This method represents the happening of an event. It invokes
	 * all attached handlers for the event.
	 * @param string $name the event name
	 * @param Event $event the event parameter
	 * @throws Exception if the event is undefined or an event handler is invalid.
	 */
	public function raiseEvent($name, $event)
	{
		$name = strtolower($name);
		if ($event instanceof Event) {
			$event->name = $name;
			$event->handled = false;
		}
		if (isset($this->_e[$name])) {
			foreach ($this->_e[$name] as $handler) {
				if (is_string($handler) || $handler instanceof \Closure) {
					call_user_func($handler, $event);
				} elseif (is_callable($handler, true)) {
					// an array: 0 - object, 1 - method name
					list($object, $method) = $handler;
					if (is_string($object)) { // static method call
						call_user_func($handler, $event);
					} elseif (method_exists($object, $method)) {
						$object->$method($event);
					} else {
						throw new Exception('Event "' . get_class($this) . '.' . $name . '" is attached with an invalid handler.');
					}
				} else {
					throw new Exception('Event "' . get_class($this) . '.' . $name . '" is attached with an invalid handler.');
				}

				// stop further handling if the event is handled
				if ($event instanceof Event && $event->handled) {
					return;
				}
			}
		} elseif (!$this->hasEvent($name)) {
			throw new Exception('Raising unknown event: ' . get_class($this) . '.' . $name);
		}
	}

	/**
	 * Returns the named behavior object.
	 * The name 'asa' stands for 'as a'.
	 * @param string $behavior the behavior name
	 * @return Behavior the behavior object, or null if the behavior does not exist
	 */
	public function asa($behavior)
	{
		return isset($this->_b[$behavior]) ? $this->_b[$behavior] : null;
	}

	/**
	 * Attaches a behavior to this component.
	 * This method will create the behavior object based on the given
	 * configuration. After that, the behavior object will be attached to
	 * this component by calling the [[Behavior::attach]] method.
	 * @param string $name the behavior's name. It should uniquely identify this behavior.
	 * @param mixed $behavior the behavior configuration. This can be one of the following:
	 *
	 *  - a [[Behavior]] object
	 *  - a string specifying the behavior class
	 *  - an object configuration array that will be passed to [[\Yii::createObject]] to create the behavior object.
	 *
	 * @return Behavior the behavior object
	 * @see detachBehavior
	 */
	public function attachBehavior($name, $behavior)
	{
		if (!($behavior instanceof Behavior)) {
			$behavior = \Yii::createObject($behavior);
		}
		$behavior->attach($this);
		return $this->_b[$name] = $behavior;
	}

	/**
	 * Attaches a list of behaviors to the component.
	 * Each behavior is indexed by its name and should be a [[Behavior]] object,
	 * a string specifying the behavior class, or an
	 * configuration array for creating the behavior.
	 * @param array $behaviors list of behaviors to be attached to the component
	 * @see attachBehavior
	 */
	public function attachBehaviors($behaviors)
	{
		foreach ($behaviors as $name => $behavior) {
			$this->attachBehavior($name, $behavior);
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
		if (isset($this->_b[$name])) {
			$this->_b[$name]->detach($this);
			$behavior = $this->_b[$name];
			unset($this->_b[$name]);
			return $behavior;
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
			$this->_b = null;
		}
	}
}
