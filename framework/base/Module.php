<?php
/**
 * Module class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use yii\util\FileHelper;

/**
 * Module is the base class for module and application classes.
 *
 * Module mainly manages application components and sub-modules that belongs to a module.
 *
 * @property string $uniqueId An ID that uniquely identifies this module among all modules within
 * the current application.
 * @property string $basePath The root directory of the module. Defaults to the directory containing the module class.
 * @property array $modules The configuration of the currently installed modules (module ID => configuration).
 * @property array $components The application components (indexed by their IDs).
 * @property array $import List of aliases to be imported. This property is write-only.
 * @property array $aliases List of aliases to be defined. This property is write-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class Module extends Component
{
	/**
	 * @var array custom module parameters (name => value).
	 */
	public $params = array();
	/**
	 * @var array the IDs of the application components that should be preloaded when this module is created.
	 */
	public $preload = array();
	/**
	 * @var string an ID that uniquely identifies this module among other modules which have the same [[parent]].
	 */
	public $id;
	/**
	 * @var Module the parent module of this module. Null if this module does not have a parent.
	 */
	public $module;
	/**
	 * @var mixed the layout that should be applied for views within this module. This refers to a view name
	 * relative to [[layoutPath]]. If this is not set, it means the layout value of the [[module|parent module]]
	 * will be taken. If this is false, layout will be disabled within this module.
	 */
	public $layout;
	/**
	 * @var array mapping from controller ID to controller configurations.
	 * Each name-value pair specifies the configuration of a single controller.
	 * A controller configuration can be either a string or an array.
	 * If the former, the string should be the class name or path alias of the controller.
	 * If the latter, the array must contain a 'class' element which specifies
	 * the controller's class name or path alias, and the rest of the name-value pairs
	 * in the array are used to initialize the corresponding controller properties. For example,
	 *
	 * ~~~
	 * array(
	 *   'account' => '@application/controllers/UserController',
	 *   'article' => array(
	 *      'class' => '@application/controllers/PostController',
	 *      'pageTitle' => 'something new',
	 *   ),
	 * )
	 * ~~~
	 */
	public $controllers = array();
	/**
	 * @return string the default route of this module. Defaults to 'default'.
	 * The route may consist of child module ID, controller ID, and/or action ID.
	 * For example, `help`, `post/create`, `admin/post/create`.
	 * If action ID is not given, it will take the default value as specified in
	 * [[Controller::defaultAction]].
	 */
	public $defaultRoute = 'default';
	/**
	 * @var string the root directory of the module.
	 */
	protected $_basePath;
	/**
	 * @var string the root directory that contains view files.
	 */
	protected $_viewPath;
	/**
	 * @var string the root directory that contains layout view files.
	 */
	protected $_layoutPath;
	/**
	 * @var string the directory containing controller classes in the module.
	 */
	protected $_controllerPath;
	/**
	 * @var array child modules of this module
	 */
	protected $_modules = array();
	/**
	 * @var array application components of this module
	 */
	protected $_components = array();

	/**
	 * Constructor.
	 * @param string $id the ID of this module
	 * @param Module $parent the parent module (if any)
	 * @param array $config name-value pairs that will be used to initialize the object properties
	 */
	public function __construct($id, $parent = null, $config = array())
	{
		$this->id = $id;
		$this->module = $parent;
		parent::__construct($config);
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
		\Yii::setAlias('@' . $this->id, $this->getBasePath());
		$this->preloadComponents();
	}

	/**
	 * Returns an ID that uniquely identifies this module among all modules within the current application.
	 * @return string the unique ID of the module.
	 */
	public function getUniqueId()
	{
		if ($this->module && !$this->module instanceof Application) {
			return $this->module->getUniqueId() . "/{$this->id}";
		} else {
			return $this->id;
		}
	}

	/**
	 * Returns the root directory of the module.
	 * It defaults to the directory containing the module class file.
	 * @return string the root directory of the module.
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
	 * @param string $path the root directory of the module. This can be either a directory name or a path alias.
	 * @throws Exception if the directory does not exist.
	 */
	public function setBasePath($path)
	{
		$this->_basePath = FileHelper::ensureDirectory($path);
	}

	/**
	 * Returns the directory that contains the controller classes.
	 * Defaults to "[[basePath]]/controllers".
	 * @return string the directory that contains the controller classes.
	 */
	public function getControllerPath()
	{
		if ($this->_controllerPath !== null) {
			return $this->_controllerPath;
		} else {
			return $this->_controllerPath = $this->getBasePath() . DIRECTORY_SEPARATOR . 'controllers';
		}
	}

	/**
	 * Sets the directory that contains the controller classes.
	 * @param string $path the directory that contains the controller classes.
	 * This can be either a directory name or a path alias.
	 * @throws Exception if the directory is invalid
	 */
	public function setControllerPath($path)
	{
		$this->_controllerPath = FileHelper::ensureDirectory($path);
	}

	/**
	 * @return string the root directory of view files. Defaults to 'moduleDir/views' where
	 * moduleDir is the directory containing the module class.
	 */
	public function getViewPath()
	{
		if ($this->_viewPath !== null) {
			return $this->_viewPath;
		} else {
			return $this->_viewPath = $this->getBasePath() . DIRECTORY_SEPARATOR . 'views';
		}
	}

	/**
	 * Sets the directory that contains the view files.
	 * @param string $path the root directory of view files.
	 * @throws Exception if the directory is invalid
	 */
	public function setViewPath($path)
	{
		$this->_viewPath = FileHelper::ensureDirectory($path);
	}

	/**
	 * @return string the root directory of layout files. Defaults to 'moduleDir/views/layouts' where
	 * moduleDir is the directory containing the module class.
	 */
	public function getLayoutPath()
	{
		if ($this->_layoutPath !== null) {
			return $this->_layoutPath;
		} else {
			return $this->_layoutPath = $this->getViewPath() . DIRECTORY_SEPARATOR . 'layouts';
		}
	}

	/**
	 * Sets the directory that contains the layout files.
	 * @param string $path the root directory of layout files.
	 * @throws Exception if the directory is invalid
	 */
	public function setLayoutPath($path)
	{
		$this->_layoutPath = FileHelper::ensureDirectory($path);
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
	 *         'class' => 'yii\db\Connection',
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

	/**
	 * Creates a controller instance based on the given route.
	 * This method tries to parse the given route (e.g. `post/create`) using the following algorithm:
	 *
	 * 1. Get the first segment in route
	 * 2. If the segment matches
	 *    - an ID in [[controllers]], create a controller instance using the corresponding configuration,
	 *      and return the controller with the rest part of the route;
	 *    - an ID in [[modules]], call the [[createController()]] method of the corresponding module.
	 *    - a controller class under [[controllerPath]], create the controller instance, and return it
	 *      with the rest part of the route;
	 *
	 * @param string $route the route which may consist module ID, controller ID and/or action ID (e.g. `post/create`)
	 * @return array|boolean the array of controller instance and action ID. False if the route cannot be resolved.
	 */
	public function createController($route)
	{
		if (($route = trim($route, '/')) === '') {
			$route = $this->defaultRoute;
		}

		if (($pos = strpos($route, '/')) !== false) {
			$id = substr($route, 0, $pos);
			$route = (string)substr($route, $pos + 1);
		} else {
			$id = $route;
			$route = '';
		}

		// Controller IDs must start with a lower-case letter and consist of word characters only
		if (!preg_match('/^[a-z][a-zA-Z0-9_]*$/', $id)) {
			return false;
		}

		if (isset($this->controllers[$id])) {
			return array(
				\Yii::createObject($this->controllers[$id], $id, $this),
				$route,
			);
		}

		if (($module = $this->getModule($id)) !== null) {
			$result = $module->createController($route);
			if ($result !== false) {
				return $result;
			}
		}

		$className = ucfirst($id) . 'Controller';
		$classFile = $this->getControllerPath() . DIRECTORY_SEPARATOR . $className . '.php';
		if (is_file($classFile)) {
			if (!class_exists($className, false)) {
				require($classFile);
			}
			if (class_exists($className, false) && is_subclass_of($className, '\yii\base\Controller')) {
				return array(
					new $className($id, $this),
					$route,
				);
			}
		}

		return false;
	}
}
