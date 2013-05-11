<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;
use yii\helpers\StringHelper;

/**
 * Module is the base class for module and application classes.
 *
 * A module represents a sub-application which contains MVC elements by itself, such as
 * models, views, controllers, etc.
 *
 * A module may consist of [[modules|sub-modules]].
 *
 * [[components|Components]] may be registered with the module so that they are globally
 * accessible within the module.
 *
 * @property string $uniqueId An ID that uniquely identifies this module among all modules within
 * the current application.
 * @property string $basePath The root directory of the module. Defaults to the directory containing the module class.
 * @property string $controllerPath The directory containing the controller classes. Defaults to "[[basePath]]/controllers".
 * @property string $viewPath The directory containing the view files within this module. Defaults to "[[basePath]]/views".
 * @property string $layoutPath The directory containing the layout view files within this module. Defaults to "[[viewPath]]/layouts".
 * @property array $modules The configuration of the currently installed modules (module ID => configuration).
 * @property array $components The components (indexed by their IDs) registered within this module.
 * @property array $import List of aliases to be imported. This property is write-only.
 * @property array $aliases List of aliases to be defined. This property is write-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
abstract class Module extends Component
{
	/**
	 * @event ActionEvent an event raised before executing a controller action.
	 * You may set [[ActionEvent::isValid]] to be false to cancel the action execution.
	 */
	const EVENT_BEFORE_ACTION = 'beforeAction';
	/**
	 * @event ActionEvent an event raised after executing a controller action.
	 */
	const EVENT_AFTER_ACTION = 'afterAction';
	/**
	 * @var array custom module parameters (name => value).
	 */
	public $params = array();
	/**
	 * @var array the IDs of the components that should be preloaded when this module is created.
	 */
	public $preload = array();
	/**
	 * @var string an ID that uniquely identifies this module among other modules which have the same [[module|parent]].
	 */
	public $id;
	/**
	 * @var Module the parent module of this module. Null if this module does not have a parent.
	 */
	public $module;
	/**
	 * @var string|boolean the layout that should be applied for views within this module. This refers to a view name
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
	 *   'account' => '@app/controllers/UserController',
	 *   'article' => array(
	 *      'class' => '@app/controllers/PostController',
	 *      'pageTitle' => 'something new',
	 *   ),
	 * )
	 * ~~~
	 */
	public $controllerMap = array();
	/**
	 * @var string the namespace that controller classes are in. Default is to use global namespace.
	 */
	public $controllerNamespace;
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
	private $_basePath;
	/**
	 * @var string the root directory that contains view files for this module
	 */
	private $_viewPath;
	/**
	 * @var string the root directory that contains layout view files for this module.
	 */
	private $_layoutPath;
	/**
	 * @var string the directory containing controller classes in the module.
	 */
	private $_controllerPath;
	/**
	 * @var array child modules of this module
	 */
	private $_modules = array();
	/**
	 * @var array components registered under this module
	 */
	private $_components = array();

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
	 * This method is overridden to support accessing components
	 * like reading module properties.
	 * @param string $name component or property name
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
	 * if the named component is loaded.
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
		$this->preloadComponents();
	}

	/**
	 * Returns an ID that uniquely identifies this module among all modules within the current application.
	 * Note that if the module is an application, an empty string will be returned.
	 * @return string the unique ID of the module.
	 */
	public function getUniqueId()
	{
		if ($this instanceof Application) {
			return '';
		} elseif ($this->module) {
			return $this->module->getUniqueId() . '/' . $this->id;
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
	 * @throws InvalidParamException if the directory does not exist.
	 */
	public function setBasePath($path)
	{
		$path = Yii::getAlias($path);
		$p = realpath($path);
		if ($p !== false && is_dir($p)) {
			$this->_basePath = $p;
		} else {
			throw new InvalidParamException("The directory does not exist: $path");
		}
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
		$this->_controllerPath = Yii::getAlias($path);
	}

	/**
	 * Returns the directory that contains the view files for this module.
	 * @return string the root directory of view files. Defaults to "[[basePath]]/view".
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
		$this->_viewPath = Yii::getAlias($path);
	}

	/**
	 * Returns the directory that contains layout view files for this module.
	 * @return string the root directory of layout files. Defaults to "[[viewPath]]/layouts".
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
		$this->_layoutPath = Yii::getAlias($path);
	}

	/**
	 * Defines path aliases.
	 * This method calls [[Yii::setAlias()]] to register the path aliases.
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
			Yii::setAlias($name, $alias);
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
				Yii::trace("Loading module: $id", __METHOD__);
				return $this->_modules[$id] = Yii::createObject($this->_modules[$id], $id, $this);
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
	 * array that can be used to create the module. In the latter case, [[Yii::createObject()]]
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
	 *         'db' => 'db',
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
	 * @param string $id component ID
	 * @return boolean whether the named component exists. Both loaded and unloaded components
	 * are considered.
	 */
	public function hasComponent($id)
	{
		return isset($this->_components[$id]);
	}

	/**
	 * Retrieves the named component.
	 * @param string $id component ID (case-sensitive)
	 * @param boolean $load whether to load the component if it is not yet loaded.
	 * @return Component|null the component instance, null if the component does not exist.
	 * @see hasComponent()
	 */
	public function getComponent($id, $load = true)
	{
		if (isset($this->_components[$id])) {
			if ($this->_components[$id] instanceof Component) {
				return $this->_components[$id];
			} elseif ($load) {
				Yii::trace("Loading component: $id", __METHOD__);
				return $this->_components[$id] = Yii::createObject($this->_components[$id]);
			}
		}
		return null;
	}

	/**
	 * Registers a component with this module.
	 * @param string $id component ID
	 * @param Component|array|null $component the component to be registered with the module. This can
	 * be one of the followings:
	 *
	 * - a [[Component]] object
	 * - a configuration array: when [[getComponent()]] is called initially for this component, the array
	 *   will be used to instantiate the component via [[Yii::createObject()]].
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
	 * Returns the registered components.
	 * @param boolean $loadedOnly whether to return the loaded components only. If this is set false,
	 * then all components specified in the configuration will be returned, whether they are loaded or not.
	 * Loaded components will be returned as objects, while unloaded components as configuration arrays.
	 * @return array the components (indexed by their IDs)
	 */
	public function getComponents($loadedOnly = false)
	{
		if ($loadedOnly) {
			$components = array();
			foreach ($this->_components as $component) {
				if ($component instanceof Component) {
					$components[] = $component;
				}
			}
			return $components;
		} else {
			return $this->_components;
		}
	}

	/**
	 * Registers a set of components in this module.
	 *
	 * Each component should be specified as a name-value pair, where
	 * name refers to the ID of the component and value the component or a configuration
	 * array that can be used to create the component. In the latter case, [[Yii::createObject()]]
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
	 *         'db' => 'db',
	 *     ),
	 * )
	 * ~~~
	 *
	 * @param array $components components (id => component configuration or instance)
	 */
	public function setComponents($components)
	{
		foreach ($components as $id => $component) {
			if (isset($this->_components[$id]['class']) && !isset($component['class'])) {
				$component['class'] = $this->_components[$id]['class'];
			}
			$this->_components[$id] = $component;
		}
	}

	/**
	 * Loads components that are declared in [[preload]].
	 */
	public function preloadComponents()
	{
		foreach ($this->preload as $id) {
			$this->getComponent($id);
		}
	}

	/**
	 * Runs a controller action specified by a route.
	 * This method parses the specified route and creates the corresponding child module(s), controller and action
	 * instances. It then calls [[Controller::runAction()]] to run the action with the given parameters.
	 * If the route is empty, the method will use [[defaultRoute]].
	 * @param string $route the route that specifies the action.
	 * @param array $params the parameters to be passed to the action
	 * @return integer the status code returned by the action execution. 0 means normal, and other values mean abnormal.
	 * @throws InvalidRouteException if the requested route cannot be resolved into an action successfully
	 */
	public function runAction($route, $params = array())
	{
		$result = $this->createController($route);
		if (is_array($result)) {
			/** @var $controller Controller */
			list($controller, $actionID) = $result;
			$oldController = Yii::$app->controller;
			Yii::$app->controller = $controller;
			$status = $controller->runAction($actionID, $params);
			Yii::$app->controller = $oldController;
			return $status;
		} else {
			throw new InvalidRouteException('Unable to resolve the request "' . trim($this->getUniqueId() . '/' . $route, '/') . '".');
		}
	}

	/**
	 * Creates a controller instance based on the controller ID.
	 *
	 * The controller is created within this module. The method first attempts to
	 * create the controller based on the [[controllerMap]] of the module. If not available,
	 * it will look for the controller class under the [[controllerPath]] and create an
	 * instance of it.
	 *
	 * @param string $route the route consisting of module, controller and action IDs.
	 * @return array|boolean If the controller is created successfully, it will be returned together
	 * with the requested action ID. Otherwise false will be returned.
	 * @throws InvalidConfigException if the controller class and its file do not match.
	 */
	public function createController($route)
	{
		if ($route === '') {
			$route = $this->defaultRoute;
		}
		if (($pos = strpos($route, '/')) !== false) {
			$id = substr($route, 0, $pos);
			$route = substr($route, $pos + 1);
		} else {
			$id = $route;
			$route = '';
		}

		$module = $this->getModule($id);
		if ($module !== null) {
			return $module->createController($route);
		}

		if (isset($this->controllerMap[$id])) {
			$controller = Yii::createObject($this->controllerMap[$id], $id, $this);
		} elseif (preg_match('/^[a-z0-9\\-_]+$/', $id)) {
			$className = StringHelper::id2camel($id) . 'Controller';
			$classFile = $this->controllerPath . DIRECTORY_SEPARATOR . $className . '.php';
			if (!is_file($classFile)) {
				return false;
			}
			$className = ltrim($this->controllerNamespace . '\\' . $className, '\\');
			Yii::$classMap[$className] = $classFile;
			if (is_subclass_of($className, 'yii\base\Controller')) {
				$controller = new $className($id, $this);
			} elseif (YII_DEBUG) {
				throw new InvalidConfigException("Controller class must extend from \\yii\\base\\Controller.");
			}
		}

		return isset($controller) ? array($controller, $route) : false;
	}

	/**
	 * This method is invoked right before an action is to be executed (after all possible filters.)
	 * You may override this method to do last-minute preparation for the action.
	 * @param Action $action the action to be executed.
	 * @return boolean whether the action should continue to be executed.
	 */
	public function beforeAction($action)
	{
		$event = new ActionEvent($action);
		$this->trigger(self::EVENT_BEFORE_ACTION, $event);
		return $event->isValid;
	}

	/**
	 * This method is invoked right after an action is executed.
	 * You may override this method to do some postprocessing for the action.
	 * @param Action $action the action just executed.
	 */
	public function afterAction($action)
	{
		$this->trigger(self::EVENT_AFTER_ACTION, new ActionEvent($action));
	}
}
