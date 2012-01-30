<?php
/**
 * Object class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * Object is the base class that implements the *property* feature.
 *
 * A property is defined by a getter method (e.g. `getLabel`),
 * and/or a setter method (e.g. `setLabel`). For example, the following
 * getter and setter methods define a property named `label`:
 *
 * ~~~
 * private $_label;
 *
 * public function getLabel()
 * {
 *	 return $this->_label;
 * }
 *
 * public function setLabel($value)
 * {
 *	 $this->_label = $value;
 * }
 * ~~~
 *
 * A property can be accessed like a member variable of an object.
 * Reading or writing a property will cause the invocation of the corresponding
 * getter or setter method. For example,
 *
 * ~~~
 * // equivalent to $label = $object->getLabel();
 * $label = $object->label;
 * // equivalent to $object->setLabel('abc');
 * $object->label = 'abc';
 * ~~~
 *
 * If a property has only a getter method and has no setter method, it is
 * considered as *read-only*. In this case, trying to modify the property value
 * will cause an exception.
 *
 * Property names are *case-insensitive*.
 *
 * One can call [[hasProperty]], [[canGetProperty]] and/or [[canSetProperty]]
 * to check the existence of a property.
 *
 * Besides the property feature, the Object class defines a static method
 * [[create]] which provides a convenient alternative way of creating a new
 * object instance.
 *
 * The Object class also defines the [[evaluateExpression]] method so that a PHP
 * expression or callback can be dynamically evaluated within the context of an object.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Object
{
	/**
	 * Constructor.
	 */
	public function __construct()
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
	 * @throws Exception if the property is not defined
	 * @see __set
	 */
	public function __get($name)
	{
		$getter = 'get' . $name;
		if (method_exists($this, $getter)) {
			return $this->$getter();
		} else {
			throw new Exception('Getting unknown property: ' . get_class($this) . '.' . $name);
		}
	}

	/**
	 * Sets value of an object property.
	 *
	 * Do not call this method directly as it is a PHP magic method that
	 * will be implicitly called when executing `$object->property = $value;`.
	 * @param string $name the property name or the event name
	 * @param mixed $value the property value
	 * @throws Exception if the property is not defined or read-only.
	 * @see __get
	 */
	public function __set($name, $value)
	{
		$setter = 'set' . $name;
		if (method_exists($this, $setter)) {
			$this->$setter($value);
		} elseif (method_exists($this, 'get' . $name)) {
			throw new Exception('Setting read-only property: ' . get_class($this) . '.' . $name);
		} else {
			throw new Exception('Setting unknown property: ' . get_class($this) . '.' . $name);
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
		if (method_exists($this, $getter)) { // property is not null
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
	 * @throws Exception if the property is read only.
	 */
	public function __unset($name)
	{
		$setter = 'set' . $name;
		if (method_exists($this, $setter)) { // write property
			$this->$setter(null);
		} elseif (method_exists($this, 'get' . $name)) {
			throw new Exception('Unsetting read-only property: ' . get_class($this) . '.' . $name);
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
	 * @return mixed the method return value
	 */
	public function __call($name, $params)
	{
		if ($this->canGetProperty($name, false)) {
			$getter = 'get' . $name;
			$func = $this->$getter;
			if ($func instanceof \Closure) {
				return call_user_func_array($func, $params);
			}
		}
		throw new Exception('Unknown method: ' . get_class($this) . "::$name()");
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
		return $this->canGetProperty($name, false) || $this->canSetProperty($name, false)
			|| $checkVar && property_exists($this, $name);
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

	/**
	 * Evaluates a PHP expression or callback under the context of this object.
	 *
	 * Valid PHP callback can be class method name in the form of
	 * array(ClassName/Object, MethodName), or anonymous function.
	 *
	 * If a PHP callback is used, the corresponding function/method signature should be
	 *
	 * ~~~
	 * function foo($param1, $param2, ..., $object) { ... }
	 * ~~~
	 *
	 * where the array elements in the second parameter to this method will be passed
	 * to the callback as `$param1`, `$param2`, ...; and the last parameter will be the object itself.
	 *
	 * If a PHP expression is used, the second parameter will be "extracted" into PHP variables
	 * that can be directly accessed in the expression.
	 * See [PHP extract](http://us.php.net/manual/en/function.extract.php)
	 * for more details. In the expression, the object can be accessed using `$this`.
	 *
	 * @param mixed $_expression_ a PHP expression or PHP callback to be evaluated.
	 * @param array $_data_ additional parameters to be passed to the above expression/callback.
	 * @return mixed the expression result
	 */
	public function evaluateExpression($_expression_, $_data_ = array())
	{
		if (is_string($_expression_)) {
			extract($_data_);
			return eval('return ' . $_expression_ . ';');
		} else {
			$_data_[] = $this;
			return call_user_func_array($_expression_, $_data_);
		}
	}

	/**
	 * Creates a new instance of the calling class.
	 *
	 * The newly created object will be initialized with the specified configuration.
	 *
	 * Extra parameters passed to this method will be used as the parameters to the object
	 * constructor.
	 *
	 * This method does the following steps to create a object:
	 *
	 * - create the object using the PHP `new` operator;
	 * - if [[Yii::objectConfig]] contains the configuration for the object class,
	 *   it will be merged with the $config parameter;
	 * - initialize the object properties using the configuration passed to this method;
	 * - call the `init` method of the object if it implements the [[yii\base\Initable]] interface.
	 *
	 * For example,
	 *
	 * ~~~
	 * class Foo extends \yii\base\Object implements \yii\base\Initable
	 * {
	 *	 public $c;
	 *	 public function __construct($a, $b)
	 *	 {
	 *		 ...
	 *	 }
	 *	 public function init()
	 *	 {
	 *		 ...
	 *	 }
	 * }
	 *
	 * $model = Foo::newInstance(array('c' => 3), 1, 2);
	 * // which is equivalent to the following lines:
	 * $model = new Foo(1, 2);
	 * $model->c = 3;
	 * $model->init();
	 * ~~~
	 *
	 * @param array $config the object configuration (name-value pairs that will be used to initialize the object)
	 * @return Object the created object
	 * @throws Exception if the configuration is invalid.
	 */
	public static function newInstance($config = array())
	{
		$class = '\\' . get_called_class();

		if (($n = func_num_args()) > 1) {
			$args = func_get_args();
			if ($n === 2) {
				$object = new $class($args[1]);
			} elseif ($n === 3) {
				$object = new $class($args[1], $args[2]);
			} elseif ($n === 4) {
				$object = new $class($args[1], $args[2], $args[3]);
			} else {
				array_shift($args); // remove $config
				$r = new \ReflectionClass($class);
				$object = $r->newInstanceArgs($args);
			}
		} else {
			$object = new $class;
		}

		if (isset(\Yii::$objectConfig[$class])) {
			$config = array_merge(\Yii::$objectConfig[$class], $config);
		}

		foreach ($config as $name => $value) {
			$object->$name = $value;
		}

		if ($object instanceof \yii\base\Initable) {
			$object->init();
		}

		return $object;
	}
}
