<?php
/**
 * Module class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * Module is the base class for module and application classes.
 *
 * Module mainly manages application components and sub-modules that belongs to a module.
 *
 * @property string $id The module ID.
 * @property string $basePath The root directory of the module. Defaults to the directory containing the module class.
 * @property Module|null $parentModule The parent module. Null if this module does not have a parent.
 * @property array $modules The configuration of the currently installed modules (module ID => configuration).
 * @property array $components The application components (indexed by their IDs).
 * @property array $import List of aliases to be imported. This property is write-only.
 * @property array $aliases List of aliases to be defined. This property is write-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class Module extends Component implements Initable
{
	/**
	 * @var array custom module parameters (name => value).
	 */
	public $params = array();
	/**
	 * @var array the IDs of the application components that should be preloaded when this module is created.
	 */
	public $preload = array();

	private $_id;
	private $_basePath;
	private $_parentModule;
	private $_modules = array();
	private $_components = array();

	/**
	 * Constructor.
	 * @param string $id the ID of this module
	 * @param Module $parent the parent module (if any)
	 */
	public function __construct($id, $parent = null)
	{
		$this->_id = $id;
		$this->_parentModule = $parent;
	}

	/**
	 * Getter magic method.
	 * This method is overridden to support accessing application components
	 * like reading module properties.
	 * @param string $name application component or property name
	 * @return mixed the named property value
	 */
	public function __get($name)
	{
		if ($this->hasComponent($name)) {
			return $this->getComponent($name);
		} else {
			return parent::__get($name);
		}
	}

	/**
	 * Checks if a property value is null.
	 * This method overrides the parent implementation by checking
	 * if the named application component is loaded.
	 * @param string $name the property name or the event name
	 * @return boolean whether the property value is null
	 */
	public function __isset($name)
	{
		if ($this->hasComponent($name)) {
			return $this->getComponent($name) !== null;
		} else {
			return parent::__isset($name);
		}
	}

	/**
	 * Initializes the module.
	 * This method is called after the module is created and initialized with property values
	 * given in configuration. The default implement will create a path alias using the module [[id]]
	 * and then call [[preloadComponents()]] to load components that are declared in [[preload]].
	 */
	public function init()
	{
		\Yii::setAlias('@' . $this->getId(), $this->getBasePath());
		$this->preloadComponents();
	}

	/**
	 * Returns the module ID.
	 * @return string the module ID.
	 */
	public function getId()
	{
		return $this->_id;
	}

	/**
	 * Sets the module ID.
	 * @param string $id the module ID
	 */
	public function setId($id)
	{
		$this->_id = $id;
	}

	/**
	 * Returns the root directory of the module.
	 * @return string the root directory of the module. Defaults to the directory containing the module class file.
	 */
	public function getBasePath()
	{
		if ($this->_basePath === null) {
			$class = new \ReflectionClass($this);
			$this->_basePath = dirname($class->getFileName());
		}
		return $this->_basePath;
	}

	/**
	 * Sets the root directory of the module.
	 * This method can only be invoked at the beginning of the constructor.
	 * @param string $path the root directory of the module.
	 * @throws Exception if the directory does not exist.
	 */
	public function setBasePath($path)
	{
		if (($p = realpath($path)) === false || !is_dir($p)) {
			throw new Exception('Invalid base path: ' . $path);
		} else {
			$this->_basePath = $p;
		}
	}

	/**
	 * Imports the specified path aliases.
	 * This method is provided so that you can import a set of path aliases when configuring a module.
	 * The path aliases will be imported by calling [[\Yii::import()]].
	 * @param array $aliases list of path aliases to be imported
	 */
	public function setImport($aliases)
	{
		foreach ($aliases as $alias) {
			\Yii::import($alias);
		}
	}

	/**
	 * Defines path aliases.
	 * This method calls [[\Yii::setAlias()]] to register the path aliases.
	 * This method is provided so that you can define path aliases when configuring a module.
	 * @param array $aliases list of path aliases to be defined. The array keys are alias names
	 * (must start with '@') and the array values are the corresponding paths or aliases.
	 * For example,
	 *
	 * ~~~
	 * array(
	 *	'@models' => '@app/models', // an existing alias
	 *	'@backend' => __DIR__ . '/../backend',  // a directory
	 * )
	 * ~~~
	 */
	public function setAliases($aliases)
	{
		foreach ($aliases as $name => $alias) {
			\Yii::setAlias($name, $alias);
		}
	}

	/**
	 * Returns the parent module.
	 * @return Module|null the parent module. Null is returned if this module does not have a parent.
	 */
	public function getParentModule()
	{
		return $this->_parentModule;
	}

	/**
	 * Checks whether the named module exists.
	 * @param string $id module ID
	 * @return boolean whether the named module exists. Both loaded and unloaded modules
	 * are considered.
	 */
	public function hasModule($id)
	{
		return isset($this->_modules[$id]);
	}

	/**
	 * Retrieves the named module.
	 * @param string $id module ID (case-sensitive)
	 * @param boolean $load whether to load the module if it is not yet loaded.
	 * @return Module|null the module instance, null if the module
	 * does not exist.
	 * @see hasModule()
	 */
	public function getModule($id, $load = true)
	{
		if (isset($this->_modules[$id])) {
			if ($this->_modules[$id] instanceof Module) {
				return $this->_modules[$id];
			} elseif ($load) {
				\Yii::trace("Loading \"$id\" module", __CLASS__);
				return $this->_modules[$id] = \Yii::createObject($this->_modules[$id], $id, $this);
			}
		}
		return null;
	}

	/**
	 * Adds a sub-module to this module.
	 * @param string $id module ID
	 * @param Module|array|null $module the sub-module to be added to this module. This can
	 * be one of the followings:
	 *
	 * - a [[Module]] object
	 * - a configuration array: when [[getModule()]] is called initially, the array
	 *   will be used to instantiate the sub-module
	 * - null: the named sub-module will be removed from this module
	 */
	public function setModule($id, $module)
	{
		if ($module === null) {
			unset($this->_modules[$id]);
		} else {
			$this->_modules[$id] = $module;
		}
	}

	/**
	 * Returns the sub-modules in this module.
	 * @param boolean $loadedOnly whether to return the loaded sub-modules only. If this is set false,
	 * then all sub-modules registered in this module will be returned, whether they are loaded or not.
	 * Loaded modules will be returned as objects, while unloaded modules as configuration arrays.
	 * @return array the modules (indexed by their IDs)
	 */
	public function getModules($loadedOnly = false)
	{
		if ($loadedOnly) {
			$modules = array();
			foreach ($this->_modules as $module) {
				if ($module instanceof Module) {
					$modules[] = $module;
				}
			}
			return $modules;
		} else {
			return $this->_modules;
		}
	}

	/**
	 * Registers sub-modules in the current module.
	 *
	 * Each sub-module should be specified as a name-value pair, where
	 * name refers to the ID of the module and value the module or a configuration
	 * array that can be used to create the module. In the latter case, [[\Yii::createObject()]]
	 * will be used to create the module.
	 *
	 * If a new sub-module has the same ID as an existing one, the existing one will be overwritten silently.
	 *
	 * The following is an example for registering two sub-modules:
	 *
	 * ~~~
	 * array(
	 *     'comment' => array(
	 *         'class' => 'app\modules\CommentModule',
	 *         'connectionID' => 'db',
	 *     ),
	 *     'booking' => array(
	 *         'class' => 'app\modules\BookingModule',
	 *     ),
	 * )
	 * ~~~
	 *
	 * @param array $modules modules (id => module configuration or instances)
	 */
	public function setModules($modules)
	{
		foreach ($modules as $id => $module) {
			$this->_modules[$id] = $module;
		}
	}

	/**
	 * Checks whether the named component exists.
	 * @param string $id application component ID
	 * @return boolean whether the named application component exists. Both loaded and unloaded components
	 * are considered.
	 */
	public function hasComponent($id)
	{
		return isset($this->_components[$id]);
	}

	/**
	 * Retrieves the named application component.
	 * @param string $id application component ID (case-sensitive)
	 * @param boolean $load whether to load the component if it is not yet loaded.
	 * @return ApplicationComponent|null the application component instance, null if the application component
	 * does not exist.
	 * @see hasComponent()
	 */
	public function getComponent($id, $load = true)
	{
		if (isset($this->_components[$id])) {
			if ($this->_components[$id] instanceof ApplicationComponent) {
				return $this->_components[$id];
			} elseif ($load) {
				\Yii::trace("Loading \"$id\" application component", __CLASS__);
				return $this->_components[$id] = \Yii::createObject($this->_components[$id]);
			}
		}
		return null;
	}

	/**
	 * Registers an application component in this module.
	 * @param string $id component ID
	 * @param ApplicationComponent|array|null $component the component to be added to the module. This can
	 * be one of the followings:
	 *
	 * - an [[ApplicationComponent]] object
	 * - a configuration array: when [[getComponent()]] is called initially for this component, the array
	 *   will be used to instantiate the component
	 * - null: the named component will be removed from the module
	 */
	public function setComponent($id, $component)
	{
		if ($component === null) {
			unset($this->_components[$id]);
		} else {
			$this->_components[$id] = $component;
		}
	}

	/**
	 * Returns the application components.
	 * @param boolean $loadedOnly whether to return the loaded components only. If this is set false,
	 * then all components specified in the configuration will be returned, whether they are loaded or not.
	 * Loaded components will be returned as objects, while unloaded components as configuration arrays.
	 * @return array the application components (indexed by their IDs)
	 */
	public function getComponents($loadedOnly = false)
	{
		if ($loadedOnly) {
			$components = array();
			foreach ($this->_components as $component) {
				if ($component instanceof ApplicationComponent) {
					$components[] = $component;
				}
			}
			return $components;
		} else {
			return $this->_components;
		}
	}

	/**
	 * Registers a set of application components in this module.
	 *
	 * Each application component should be specified as a name-value pair, where
	 * name refers to the ID of the component and value the component or a configuration
	 * array that can be used to create the component. In the latter case, [[\Yii::createObject()]]
	 * will be used to create the component.
	 *
	 * If a new component has the same ID as an existing one, the existing one will be overwritten silently.
	 *
	 * The following is an example for setting two components:
	 *
	 * ~~~
	 * array(
	 *     'db' => array(
	 *         'class' => 'yii\db\dao\Connection',
	 *         'dsn' => 'sqlite:path/to/file.db',
	 *     ),
	 *     'cache' => array(
	 *         'class' => 'yii\caching\DbCache',
	 *         'connectionID' => 'db',
	 *     ),
	 * )
	 * ~~~
	 *
	 * @param array $components application components (id => component configuration or instance)
	 */
	public function setComponents($components)
	{
		foreach ($components as $id => $component) {
			$this->_components[$id] = $component;
		}
	}

	/**
	 * Loads application components that are declared in [[preload]].
	 */
	public function preloadComponents()
	{
		foreach ($this->preload as $id) {
			$this->getComponent($id);
		}
	}
}
