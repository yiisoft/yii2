<?php
/**
 * Object class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * Object is the base class that provides the *property* feature.
 *
 * @include @yii/docs/base-Object.md
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Object
{
	/**
	 * Constructor.
	 * The default implementation does two things:
	 *
	 * - Initializes the object with the given configuration `$config`.
	 * - Call [[init()]].
	 *
	 * If this method is overridden in a child class, it is recommended that
	 *
	 * - the last parameter of the constructor is a configuration array, like `$config` here.
	 * - call the parent implementation at the end of the constructor.
	 *
	 * @param array $config name-value pairs that will be used to initialize the object properties
	 */
	public function __construct($config = array())
	{
		foreach ($config as $name => $value) {
			$this->$name = $value;
		}
		$this->init();
	}

	/**
	 * Initializes the object.
	 * This method is invoked at the end of the constructor after the object is initialized with the
	 * given configuration.
	 */
	public function init()
	{
	}

	/**
	 * Returns the value of an object property.
	 *
	 * Do not call this method directly as it is a PHP magic method that
	 * will be implicitly called when executing `$value = $object->property;`.
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
			return $this->$getter();
		} else {
			throw new BadPropertyException('Getting unknown property: ' . get_class($this) . '.' . $name);
		}
	}

	/**
	 * Sets value of an object property.
	 *
	 * Do not call this method directly as it is a PHP magic method that
	 * will be implicitly called when executing `$object->property = $value;`.
	 * @param string $name the property name or the event name
	 * @param mixed $value the property value
	 * @throws BadPropertyException if the property is not defined or read-only.
	 * @see __get
	 */
	public function __set($name, $value)
	{
		$setter = 'set' . $name;
		if (method_exists($this, $setter)) {
			$this->$setter($value);
		} elseif (method_exists($this, 'get' . $name)) {
			throw new BadPropertyException('Setting read-only property: ' . get_class($this) . '.' . $name);
		} else {
			throw new BadPropertyException('Setting unknown property: ' . get_class($this) . '.' . $name);
		}
	}

	/**
	 * Checks if the named property is set (not null).
	 *
	 * Do not call this method directly as it is a PHP magic method that
	 * will be implicitly called when executing `isset($object->property)`.
	 *
	 * Note that if the property is not defined, false will be returned.
	 * @param string $name the property name or the event name
	 * @return boolean whether the named property is set (not null).
	 */
	public function __isset($name)
	{
		$getter = 'get' . $name;
		if (method_exists($this, $getter)) {
			return $this->$getter() !== null;
		} else {
			return false;
		}
	}

	/**
	 * Sets an object property to null.
	 *
	 * Do not call this method directly as it is a PHP magic method that
	 * will be implicitly called when executing `unset($object->property)`.
	 *
	 * Note that if the property is not defined, this method will do nothing.
	 * If the property is read-only, it will throw an exception.
	 * @param string $name the property name
	 * @throws BadPropertyException if the property is read only.
	 */
	public function __unset($name)
	{
		$setter = 'set' . $name;
		if (method_exists($this, $setter)) {
			$this->$setter(null);
		} elseif (method_exists($this, 'get' . $name)) {
			throw new BadPropertyException('Unsetting read-only property: ' . get_class($this) . '.' . $name);
		}
	}

	/**
	 * Calls the named method which is not a class method.
	 * If the name refers to a component property whose value is
	 * an anonymous function, the method will execute the function.
	 *
	 * Do not call this method directly as it is a PHP magic method that
	 * will be implicitly called when an unknown method is being invoked.
	 * @param string $name the method name
	 * @param array $params method parameters
	 * @throws BadMethodException when calling unknown method
	 * @return mixed the method return value
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
		throw new BadMethodException('Unknown method: ' . get_class($this) . "::$name()");
	}

	/**
	 * Returns a value indicating whether a property is defined.
	 * A property is defined if there is a getter or setter method
	 * defined in the class. Note that property names are case-insensitive.
	 * @param string $name the property name
	 * @param boolean $checkVar whether to treat member variables as properties
	 * @return boolean whether the property is defined
	 * @see canGetProperty
	 * @see canSetProperty
	 */
	public function hasProperty($name, $checkVar = true)
	{
		return $this->canGetProperty($name, $checkVar) || $this->canSetProperty($name, false);
	}

	/**
	 * Returns a value indicating whether a property can be read.
	 * A property can be read if the class has a getter method
	 * for the property name. Note that property name is case-insensitive.
	 * @param string $name the property name
	 * @param boolean $checkVar whether to treat member variables as properties
	 * @return boolean whether the property can be read
	 * @see canSetProperty
	 */
	public function canGetProperty($name, $checkVar = true)
	{
		return method_exists($this, 'get' . $name) || $checkVar && property_exists($this, $name);
	}

	/**
	 * Returns a value indicating whether a property can be set.
	 * A property can be written if the class has a setter method
	 * for the property name. Note that property name is case-insensitive.
	 * @param string $name the property name
	 * @param boolean $checkVar whether to treat member variables as properties
	 * @return boolean whether the property can be written
	 * @see canGetProperty
	 */
	public function canSetProperty($name, $checkVar = true)
	{
		return method_exists($this, 'set' . $name) || $checkVar && property_exists($this, $name);
	}
}
