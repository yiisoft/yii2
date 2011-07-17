<?php
/**
 * Component class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * Component is the base class for all components in Yii.
 *
 * Component implements the basis for *properties*, *events* and *behaviors*.
 *
 * A property is defined by a getter method (e.g. `getLabel`),
 * and/or a setter method (e.g. `setLabel`). For example, the following
 * getter and setter methods define a property named `label`:
 *
 * ~~~php
 * private $_label;
 *
 * public function getLabel()
 * {
 *     return $this->_label;
 * }
 *
 * public function setLabel($value)
 * {
 *     $this->_label = $value;
 * }
 * ~~~
 *
 * A property can be accessed like a member variable of an object.
 * Reading or writing a property will cause the invocation of the corresponding
 * getter or setter method. For example,
 *
 * ~~~php
 * // equivalent to $label = $component->getLabel();
 * $label = $component->label;
 * // equivalent to $component->setLabel('abc');
 * $component->label = 'abc';
 * ~~~
 *
 *
 * An event is defined by the presence of a method whose name starts with `on`.
 * The event name is the method name. When an event is raised, functions
 * (called *event handlers*) attached to the event will be invoked automatically.
 * The `on` method is typically declared like the following:
 *
 * ~~~php
 * public function onClick($event)
 * {
 *     $this->raiseEvent('onClick', $event);
 * }
 * ~~~
 *
 * An event can be raised by calling the [[raiseEvent]] method, upon which
 * the attached event handlers will be invoked automatically in the order they
 * are attached to the event. In the above example, if we call the `onClick` method,
 * an `onClick` event will be raised.
 *
 * An event handler should be defined with the following signature:
 *
 * ~~~php
 * public function foo($event) { ... }
 * ~~~
 *
 * where `$event` is an [[Event]] object which  includes parameters associated with the event.
 *
 * To attach an event handler to an event, call [[attachEventHandler]].
 * Alternatively, you can also do the following:
 *
 * ~~~php
 * $component->onClick = $callback;
 * // or $component->onClick->add($callback);
 * ~~~
 *
 * where `$callback` refers to a valid PHP callback. Some examples of `$callback` are:
 *
 * ~~~php
 * 'handleOnClick'                    // handleOnClick() is a global function
 * array($object, 'handleOnClick')    // $object->handleOnClick()
 * array('Page', 'handleOnClick')     // Page::handleOnClick()
 * function($event) { ... }           // anonymous function
 * ~~~
 *
 * Both property names and event names are *case-insensitive*.
 *
 * A behavior is an object implementing the [[IBehavior]] interface. It is usually attached
 * to a component instance of {@link IBehavior} which is attached to a component. The methods of
 * the behavior can be invoked as if they belong to the component. Multiple behaviors
 * can be attached to the same component.
 *
 * To attach a behavior to a component, call {@link attachBehavior}; and to detach the behavior
 * from the component, call {@link detachBehavior}.
 *
 * A behavior can be temporarily enabled or disabled by calling {@link enableBehavior}
 * or {@link disableBehavior}, respectively. When disabled, the behavior methods cannot
 * be invoked via the component.
 *
 * Starting from version 1.1.0, a behavior's properties (either its public member variables or
 * its properties defined via getters and/or setters) can be accessed through the component it
 * is attached to.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: Component.php 3204 2011-05-05 21:36:32Z alexander.makarow $
 * @package system.base
 * @since 1.0
 */
class Component
{
	private $_e;
	private $_b;

	/**
	 * Returns a property value, an event handler list or a behavior based on its name.
	 * Do not call this method. This is a PHP magic method that we override
	 * to allow using the following syntax to read a property or obtain event handlers:
	 * <pre>
	 * $value=$component->propertyName;
	 * $handlers=$component->eventName;
	 * </pre>
	 * @param string $name the property name or event name
	 * @return mixed the property value, event handlers attached to the event, or the named behavior (since version 1.0.2)
	 * @throws CException if the property or event is not defined
	 * @see __set
	 */
	public function __get($name)
	{
		$getter = 'get' . $name;
		if (method_exists($this, $getter)) { // read property, e.g. getName()
			return $this->$getter();
		}
		elseif (method_exists($this, $name) && strncasecmp($name, 'on', 2) === 0) { // event, e.g. onClick()
			$name = strtolower($name);
			if (!isset($this->_e[$name])) {
				$this->_e[$name] = new \yii\collections\Vector;
			}
			return $this->_e[$name];
		}
		elseif (isset($this->_b[$name])) { // behavior
			return $this->_b[$name];
		}
		elseif (is_array($this->_b)) { // a behavior property
			foreach ($this->_b as $object) {
				if ($object->getEnabled() && (property_exists($object, $name) || $object->canGetProperty($name))) {
					return $object->$name;
				}
			}
		}
		throw new Exception('Getting unknown property: ' . get_class($this) . '.' $name);
	}

	/**
	 * Sets value of a component property.
	 * Do not call this method. This is a PHP magic method that we override
	 * to allow using the following syntax to set a property or attach an event handler
	 * <pre>
	 * $this->propertyName=$value;
	 * $this->eventName=$callback;
	 * </pre>
	 * @param string $name the property name or the event name
	 * @param mixed $value the property value or callback
	 * @throws CException if the property/event is not defined or the property is read only.
	 * @see __get
	 */
	public function __set($name, $value)
	{
		$setter = 'set' . $name;
		if (method_exists($this, $setter)) {
			return $this->$setter($value);
		}
		elseif (method_exists($this, $name) && strncasecmp($name, 'on', 2) === 0) {
			$name = strtolower($name);
			if (!isset($this->_e[$name])) {
				$this->_e[$name] = new \yii\collections\Vector;
			}
			return $this->_e[$name]->add($value);
		}
		elseif (is_array($this->_b)) {
			foreach ($this->_b as $object) {
				if ($object->getEnabled() && (property_exists($object, $name) || $object->canSetProperty($name))) {
					return $object->$name = $value;
				}
			}
		}
		if (method_exists($this, 'get' . $name)) {
			throw new Exception('Setting read-only property: ' . get_class($this) . '.' $name);
		}
		else {
			throw new Exception('Setting unknown property: ' . get_class($this) . '.' $name);
		}
	}

	/**
	 * Checks if a property value is null.
	 * Do not call this method. This is a PHP magic method that we override
	 * to allow using isset() to detect if a component property is set or not.
	 * @param string $name the property name or the event name
	 * @since 1.0.1
	 */
	public function __isset($name)
	{
		$getter = 'get' . $name;
		if (method_exists($this, $getter)) { // property is not null
			return $this->$getter() !== null;
		}
		elseif (method_exists($this, $name) && strncasecmp($name, 'on', 2) === 0) { // has event handler
			$name = strtolower($name);
			return isset($this->_e[$name]) && $this->_e[$name]->getCount();
		}
 		elseif (isset($this->_b[$name])) { // has behavior
 			return true;
 		}
		elseif (is_array($this->_b)) {
			foreach ($this->_b as $object) {
				if ($object->getEnabled() && (property_exists($object, $name) || $object->canGetProperty($name))) {
					return $object->$name !== null;
				}
			}
		}
		return false;
	}

	/**
	 * Sets a component property to be null.
	 * Do not call this method. This is a PHP magic method that we override
	 * to allow using unset() to set a component property to be null.
	 * @param string $name the property name or the event name
	 * @throws CException if the property is read only.
	 * @since 1.0.1
	 */
	public function __unset($name)
	{
		$setter = 'set' . $name;
		if (method_exists($this, $setter)) {
			$this->$setter(null);
		}
		elseif (method_exists($this, $name) && strncasecmp($name, 'on', 2) === 0) {
			unset($this->_e[strtolower($name)]);
		}
		elseif (isset($this->_b[$name])) {
			$this->detachBehavior($name);
		}
		elseif (is_array($this->_b)) {
			foreach ($this->_b as $object) {
				if ($object->getEnabled()) {
					if (property_exists($object, $name)) {
						return $object->$name = null;
					}
					elseif ($object->canSetProperty($name)) {
						return $object->$setter(null);
					}
				}
			}
		}
		elseif (method_exists($this, 'get' . $name)) {
			throw new Exception('Unsetting read-only property: ' . get_class($this) . '.' $name);
		}
	}

	/**
	 * Calls the named method which is not a class method.
	 * Do not call this method. This is a PHP magic method that we override
	 * to implement the behavior feature.
	 * @param string $name the method name
	 * @param array $parameters method parameters
	 * @return mixed the method return value
	 * @since 1.0.2
	 */
	public function __call($name, $parameters)
	{
		if ($this->_b !== null)
		{
			foreach ($this->_b as $object)
			{
				if ($object->getEnabled() && method_exists($object, $name)) {
					return call_user_func_array(array($object, $name), $parameters);
				}
			}
		}
		if ($this->canGetProperty($name)) {
			$func = $this->$name;
			if ($func instanceof \Closure) {
				return call_user_func_array($func, $parameters);
			}
		}
		throw new Exception('Unknown method: ' . get_class($this) . '::' $name . '()');
	}

	/**
	 * Returns the named behavior object.
	 * The name 'asa' stands for 'as a'.
	 * @param string $behavior the behavior name
	 * @return IBehavior the behavior object, or null if the behavior does not exist
	 * @since 1.0.2
	 */
	public function asa($behavior)
	{
		return isset($this->_b[$behavior]) ? $this->_b[$behavior] : null;
	}

	/**
	 * Attaches a list of behaviors to the component.
	 * Each behavior is indexed by its name and should be an instance of
	 * {@link IBehavior}, a string specifying the behavior class, or an
	 * array of the following structure:
	 * <pre>
	 * array(
	 *     'class'=>'path.to.BehaviorClass',
	 *     'property1'=>'value1',
	 *     'property2'=>'value2',
	 * )
	 * </pre>
	 * @param array $behaviors list of behaviors to be attached to the component
	 * @since 1.0.2
	 */
	public function attachBehaviors($behaviors)
	{
		foreach ($behaviors as $name => $behavior)
			$this->attachBehavior($name, $behavior);
	}

	/**
	 * Detaches all behaviors from the component.
	 * @since 1.0.2
	 */
	public function detachBehaviors()
	{
		if ($this->_b !== null)
		{
			foreach ($this->_b as $name => $behavior)
				$this->detachBehavior($name);
			$this->_b = null;
		}
	}

	/**
	 * Attaches a behavior to this component.
	 * This method will create the behavior object based on the given
	 * configuration. After that, the behavior object will be initialized
	 * by calling its {@link IBehavior::attach} method.
	 * @param string $name the behavior's name. It should uniquely identify this behavior.
	 * @param mixed $behavior the behavior configuration. This is passed as the first
	 * parameter to {@link YiiBase::createComponent} to create the behavior object.
	 * @return IBehavior the behavior object
	 * @since 1.0.2
	 */
	public function attachBehavior($name, $behavior)
	{
		if (!($behavior instanceof IBehavior))
			$behavior = Yii::createComponent($behavior);
		$behavior->setEnabled(true);
		$behavior->attach($this);
		return $this->_b[$name] = $behavior;
	}

	/**
	 * Detaches a behavior from the component.
	 * The behavior's {@link IBehavior::detach} method will be invoked.
	 * @param string $name the behavior's name. It uniquely identifies the behavior.
	 * @return IBehavior the detached behavior. Null if the behavior does not exist.
	 * @since 1.0.2
	 */
	public function detachBehavior($name)
	{
		if (isset($this->_b[$name]))
		{
			$this->_b[$name]->detach($this);
			$behavior = $this->_b[$name];
			unset($this->_b[$name]);
			return $behavior;
		}
	}

	/**
	 * Enables all behaviors attached to this component.
	 * @since 1.0.2
	 */
	public function enableBehaviors()
	{
		if ($this->_b !== null)
		{
			foreach ($this->_b as $behavior)
				$behavior->setEnabled(true);
		}
	}

	/**
	 * Disables all behaviors attached to this component.
	 * @since 1.0.2
	 */
	public function disableBehaviors()
	{
		if ($this->_b !== null)
		{
			foreach ($this->_b as $behavior)
				$behavior->setEnabled(false);
		}
	}

	/**
	 * Enables an attached behavior.
	 * A behavior is only effective when it is enabled.
	 * A behavior is enabled when first attached.
	 * @param string $name the behavior's name. It uniquely identifies the behavior.
	 * @since 1.0.2
	 */
	public function enableBehavior($name)
	{
		if (isset($this->_b[$name]))
			$this->_b[$name]->setEnabled(true);
	}

	/**
	 * Disables an attached behavior.
	 * A behavior is only effective when it is enabled.
	 * @param string $name the behavior's name. It uniquely identifies the behavior.
	 * @since 1.0.2
	 */
	public function disableBehavior($name)
	{
		if (isset($this->_b[$name]))
			$this->_b[$name]->setEnabled(false);
	}

	/**
	 * Determines whether a property is defined.
	 * A property is defined if there is a getter or setter method
	 * defined in the class. Note, property names are case-insensitive.
	 * @param string $name the property name
	 * @return boolean whether the property is defined
	 * @see canGetProperty
	 * @see canSetProperty
	 */
	public function hasProperty($name)
	{
		return $this->canGetProperty($name) || $this->canSetProperty($name);
	}

	/**
	 * Determines whether a property can be read.
	 * A property can be read if the class has a getter method
	 * for the property name. Note, property name is case-insensitive.
	 * @param string $name the property name
	 * @return boolean whether the property can be read
	 * @see canSetProperty
	 */
	public function canGetProperty($name)
	{
		return method_exists($this, 'get' . $name);
	}

	/**
	 * Determines whether a property can be set.
	 * A property can be written if the class has a setter method
	 * for the property name. Note, property name is case-insensitive.
	 * @param string $name the property name
	 * @return boolean whether the property can be written
	 * @see canGetProperty
	 */
	public function canSetProperty($name)
	{
		return method_exists($this, 'set' . $name);
	}

	/**
	 * Determines whether an event is defined.
	 * An event is defined if the class has a method named like 'onXXX'.
	 * Note, event name is case-insensitive.
	 * @param string $name the event name
	 * @return boolean whether an event is defined
	 */
	public function hasEvent($name)
	{
		return method_exists($this, $name) && strncasecmp($name, 'on', 2)===0;
	}

	/**
	 * Checks whether the named event has attached handlers.
	 * @param string $name the event name
	 * @return boolean whether an event has been attached one or several handlers
	 */
	public function hasEventHandler($name)
	{
		$name = strtolower($name);
		return isset($this->_e[$name]) && $this->_e[$name]->getCount() > 0;
	}

	/**
	 * Returns the list of attached event handlers for an event.
	 * @param string $name the event name
	 * @return CList list of attached event handlers for the event
	 * @throws CException if the event is not defined
	 */
	public function getEventHandlers($name)
	{
		if ($this->hasEvent($name))
		{
			$name = strtolower($name);
			if (!isset($this->_e[$name]))
				$this->_e[$name] = new CList;
			return $this->_e[$name];
		}
		else
			throw new CException(Yii::t('yii', 'Event "{class}.{event}" is not defined.',
				array('{class}' => get_class($this), '{event}' => $name)));
	}

	/**
	 * Attaches an event handler to an event.
	 *
	 * An event handler must be a valid PHP callback, i.e., a string referring to
	 * a global function name, or an array containing two elements with
	 * the first element being an object and the second element a method name
	 * of the object.
	 *
	 * An event handler must be defined with the following signature,
	 * <pre>
	 * function handlerName($event) {}
	 * </pre>
	 * where $event includes parameters associated with the event.
	 *
	 * This is a convenient method of attaching a handler to an event.
	 * It is equivalent to the following code:
	 * <pre>
	 * $component->getEventHandlers($eventName)->add($eventHandler);
	 * </pre>
	 *
	 * Using {@link getEventHandlers}, one can also specify the excution order
	 * of multiple handlers attaching to the same event. For example:
	 * <pre>
	 * $component->getEventHandlers($eventName)->insertAt(0,$eventHandler);
	 * </pre>
	 * makes the handler to be invoked first.
	 *
	 * @param string $name the event name
	 * @param callback $handler the event handler
	 * @throws CException if the event is not defined
	 * @see detachEventHandler
	 */
	public function attachEventHandler($name, $handler)
	{
		$this->getEventHandlers($name)->add($handler);
	}

	/**
	 * Detaches an existing event handler.
	 * This method is the opposite of {@link attachEventHandler}.
	 * @param string $name event name
	 * @param callback $handler the event handler to be removed
	 * @return boolean if the detachment process is successful
	 * @see attachEventHandler
	 */
	public function detachEventHandler($name, $handler)
	{
		if ($this->hasEventHandler($name))
			return $this->getEventHandlers($name)->remove($handler) !== false;
		else
			return false;
	}

	/**
	 * Raises an event.
	 * This method represents the happening of an event. It invokes
	 * all attached handlers for the event.
	 * @param string $name the event name
	 * @param CEvent $event the event parameter
	 * @throws CException if the event is undefined or an event handler is invalid.
	 */
	public function raiseEvent($name, $event)
	{
		$name = strtolower($name);
		if (isset($this->_e[$name]))
		{
			foreach ($this->_e[$name] as $handler)
			{
				if (is_string($handler))
					call_user_func($handler, $event);
				elseif (is_callable($handler, true))
				{
					if (is_array($handler))
					{
						// an array: 0 - object, 1 - method name
						list($object, $method) = $handler;
						if (is_string($object))	// static method call
							call_user_func($handler, $event);
						elseif (method_exists($object, $method))
							$object->$method($event);
						else
							throw new CException(Yii::t('yii', 'Event "{class}.{event}" is attached with an invalid handler "{handler}".',
								array('{class}' => get_class($this), '{event}' => $name, '{handler}' => $handler[1])));
					}
					else // PHP 5.3: anonymous function
						call_user_func($handler, $event);
				}
				else
					throw new CException(Yii::t('yii', 'Event "{class}.{event}" is attached with an invalid handler "{handler}".',
						array('{class}' => get_class($this), '{event}' => $name, '{handler}' => gettype($handler))));
				// stop further handling if param.handled is set true
				if (($event instanceof CEvent) && $event->handled)
					return;
			}
		}
		elseif (YII_DEBUG && !$this->hasEvent($name))
			throw new CException(Yii::t('yii', 'Event "{class}.{event}" is not defined.',
				array('{class}' => get_class($this), '{event}' => $name)));
	}

	/**
	 * Evaluates a PHP expression or callback under the context of this component.
	 *
	 * Valid PHP callback can be class method name in the form of
	 * array(ClassName/Object, MethodName), or anonymous function (only available in PHP 5.3.0 or above).
	 *
	 * If a PHP callback is used, the corresponding function/method signature should be
	 * <pre>
	 * function foo($param1, $param2, ..., $component) { ... }
	 * </pre>
	 * where the array elements in the second parameter to this method will be passed
	 * to the callback as $param1, $param2, ...; and the last parameter will be the component itself.
	 *
	 * If a PHP expression is used, the second parameter will be "extracted" into PHP variables
	 * that can be directly accessed in the expression. See {@link http://us.php.net/manual/en/function.extract.php PHP extract}
	 * for more details. In the expression, the component object can be accessed using $this.
	 *
	 * @param mixed $_expression_ a PHP expression or PHP callback to be evaluated.
	 * @param array $_data_ additional parameters to be passed to the above expression/callback.
	 * @return mixed the expression result
	 * @since 1.1.0
	 */
	public function evaluateExpression($_expression_, $_data_ = array())
	{
		if (is_string($_expression_))
		{
			extract($_data_);
			return eval('return ' . $_expression_ . ';');
		}
		else
		{
			$_data_[] = $this;
			return call_user_func_array($_expression_, $_data_);
		}
	}
}
