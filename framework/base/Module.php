<?php
/**
 * Module class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * Module is the base class for module and application classes.
 *
 * Module mainly manages application components and sub-modules.
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
	 * @var array the IDs of the application components that should be preloaded.
	 */
	public $preload = array();
	/**
	 * @var array the behaviors that should be attached to the module.
	 * The behaviors will be attached to the module when [[init]] is called.
	 * Please refer to [[Model::behaviors]] on how to specify the value of this property.
	 */
	public $behaviors = array();

	private $_id;
	private $_parentModule;
	private $_basePath;
	private $_modulePath;
	private $_params;
	private $_modules = array();
	private $_moduleConfig = array();
	private $_components = array();
	private $_componentConfig = array();


	/**
	 * Constructor.
	 * @param string $id the ID of this module
	 * @param CModule $parent the parent module (if any)
	 * @param mixed $config the module configuration. It can be either an array or
	 * the path of a PHP file returning the configuration array.
	 */
	public function __construct($id, $parent, $config = null)
	{
		$this->_id = $id;
		$this->_parentModule = $parent;

		// set basePath at early as possible to avoid trouble
		if (is_string($config)) {
			$config = require($config);
		}
		if (isset($config['basePath'])) {
			$this->setBasePath($config['basePath']);
			unset($config['basePath']);
		}
		Yii::setPathOfAlias($id, $this->getBasePath());

		$this->preinit();

		$this->configure($config);
		$this->attachBehaviors($this->behaviors);
		$this->preloadComponents();

		$this->init();
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
		}
		else {
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
		}
		else {
			return parent::__isset($name);
		}
	}

	/**
	 * Returns a list of behaviors that this model should behave as.
	 * The return value of this method should be an array of behavior configurations
	 * indexed by behavior names. For more details, please refer to [[Model::behaviors]].
	 *
	 * The declared behaviors will be attached to the module when [[init]] is called.
	 * @return array the behavior configurations.
	 */
	public function behaviors()
	{
		return array();
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
	 * @return string the root directory of the module. Defaults to the directory containing the module class.
	 */
	public function getBasePath()
	{
		if ($this->_basePath === null) {
			$class = new ReflectionClass($this);
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
		if (($this->_basePath = realpath($path)) === false || !is_dir($this->_basePath)) {
			throw new Exception('Invalid base path: ' . $path);
		}
	}

	/**
	 * Returns the directory that contains child modules.
	 * @return string the directory that contains child modules. Defaults to the `modules` subdirectory under [[basePath]].
	 */
	public function getModulePath()
	{
		if ($this->_modulePath !== null) {
			return $this->_modulePath;
		}
		else {
			return $this->_modulePath = $this->getBasePath() . DIRECTORY_SEPARATOR . 'modules';
		}
	}

	/**
	 * Sets the directory that contains child modules.
	 * @param string $value the directory that contains child modules.
	 * @throws Exception if the directory is invalid
	 */
	public function setModulePath($value)
	{
		if (($this->_modulePath = realpath($value)) === false || !is_dir($this->_modulePath)) {
			throw new Exception('Invalid module path: ' . $value);
		}
	}

	/**
	 * Imports the specified path aliases.
	 * This method is provided so that you can import a set of path aliases by module configuration.
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
	 * This method calls [[\Yii::setPathOfAlias]] to register the path aliases.
	 * This method is provided so that you can define path aliases by module configuration.
	 * @param array $aliases list of path aliases to be defined. The array keys are alias names
	 * (must start with '@') while the array values are the corresponding paths or aliases.
	 * For example,
	 *
	 * ~~~
	 * array(
	 *	'@models' => '@app/models',			 // an existing alias
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
	 * @return CModule the parent module. Null if this module does not have a parent.
	 */
	public function getParentModule()
	{
		return $this->_parentModule;
	}

	/**
	 * Retrieves the named application module.
	 * The module has to be declared in {@link modules}. A new instance will be created
	 * when calling this method with the given ID for the first time.
	 * @param string $id application module ID (case-sensitive)
	 * @return CModule the module instance, null if the module is disabled or does not exist.
	 */
	public function getModule($id)
	{
		if (isset($this->_modules[$id]) || array_key_exists($id, $this->_modules)) {
			return $this->_modules[$id];
		}
		elseif (isset($this->_moduleConfig[$id]))
		{
			$config = $this->_moduleConfig[$id];
			if (!isset($config['enabled']) || $config['enabled']) {
				\Yii::trace("Loading \"$id\" module", 'system.base.CModule');
				$class = $config['class'];
				unset($config['class'], $config['enabled']);
				if ($this === \Yii::$app) {
					$module = \Yii::createObject($class, $id, null, $config);
				}
				else
				{
					$module = \Yii::createObject($class, $this->getId() . '/' . $id, $this, $config);
				}
				return $this->_modules[$id] = $module;
			}
		}
	}

	/**
	 * Returns a value indicating whether the specified module is installed.
	 * @param string $id the module ID
	 * @return boolean whether the specified module is installed.
	 */
	public function hasModule($id)
	{
		return isset($this->_moduleConfig[$id]) || isset($this->_modules[$id]);
	}

	/**
	 * Returns the configuration of the currently installed modules.
	 * @return array the configuration of the currently installed modules (module ID => configuration)
	 */
	public function getModules()
	{
		return $this->_moduleConfig;
	}

	/**
	 * Configures the sub-modules of this module.
	 *
	 * Call this method to declare sub-modules and configure them with their initial property values.
	 * The parameter should be an array of module configurations. Each array element represents a single module,
	 * which can be either a string representing the module ID or an ID-configuration pair representing
	 * a module with the specified ID and the initial property values.
	 *
	 * For example, the following array declares two modules:
	 * <pre>
	 * array(
	 *	 'admin',				// a single module ID
	 *	 'payment'=>array(	   // ID-configuration pair
	 *		 'server'=>'paymentserver.com',
	 *	 ),
	 * )
	 * </pre>
	 *
	 * By default, the module class is determined using the expression <code>ucfirst($moduleID).'Module'</code>.
	 * And the class file is located under <code>modules/$moduleID</code>.
	 * You may override this default by explicitly specifying the 'class' option in the configuration.
	 *
	 * You may also enable or disable a module by specifying the 'enabled' option in the configuration.
	 *
	 * @param array $modules module configurations.
	 */
	public function setModules($modules)
	{
		foreach ($modules as $id => $module)
		{
			if (is_int($id)) {
				$id = $module;
				$module = array();
			}
			if (!isset($module['class'])) {
				Yii::setPathOfAlias($id, $this->getModulePath() . DIRECTORY_SEPARATOR . $id);
				$module['class'] = $id . '.' . ucfirst($id) . 'Module';
			}

			if (isset($this->_moduleConfig[$id])) {
				$this->_moduleConfig[$id] = CMap::mergeArray($this->_moduleConfig[$id], $module);
			}
			else
			{
				$this->_moduleConfig[$id] = $module;
			}
		}
	}

	/**
	 * Checks whether the named component exists.
	 * @param string $id application component ID
	 * @return boolean whether the named application component exists (including both loaded and disabled.)
	 */
	public function hasComponent($id)
	{
		return isset($this->_components[$id]) || isset($this->_componentConfig[$id]);
	}

	/**
	 * Retrieves the named application component.
	 * @param string $id application component ID (case-sensitive)
	 * @param boolean $createIfNull whether to create the component if it doesn't exist yet. This parameter
	 * has been available since version 1.0.6.
	 * @return IApplicationComponent the application component instance, null if the application component is disabled or does not exist.
	 * @see hasComponent
	 */
	public function getComponent($id, $createIfNull = true)
	{
		if (isset($this->_components[$id])) {
			return $this->_components[$id];
		}
		elseif (isset($this->_componentConfig[$id]) && $createIfNull)
		{
			$config = $this->_componentConfig[$id];
			if (!isset($config['enabled']) || $config['enabled']) {
				\Yii::trace("Loading \"$id\" application component", 'system.CModule');
				unset($config['enabled']);
				$component = \Yii::createObject($config);
				return $this->_components[$id] = $component;
			}
		}
	}

	/**
	 * Puts a component under the management of the module.
	 * The component will be initialized by calling its {@link CApplicationComponent::init() init()}
	 * method if it has not done so.
	 * @param string $id component ID
	 * @param IApplicationComponent $component the component to be added to the module.
	 * If this parameter is null, it will unload the component from the module.
	 */
	public function setComponent($id, $component)
	{
		if ($component === null) {
			unset($this->_components[$id]);
		}
		else {
			$this->_components[$id] = $component;
			if (!$component->getIsInitialized()) {
				$component->init();
			}
		}
	}

	/**
	 * Returns the application components.
	 * @param boolean $loadedOnly whether to return the loaded components only. If this is set false,
	 * then all components specified in the configuration will be returned, whether they are loaded or not.
	 * Loaded components will be returned as objects, while unloaded components as configuration arrays.
	 * This parameter has been available since version 1.1.3.
	 * @return array the application components (indexed by their IDs)
	 */
	public function getComponents($loadedOnly = true)
	{
		if ($loadedOnly) {
			return $this->_components;
		}
		else {
			return array_merge($this->_componentConfig, $this->_components);
		}
	}

	/**
	 * Sets the application components.
	 *
	 * When a configuration is used to specify a component, it should consist of
	 * the component's initial property values (name-value pairs). Additionally,
	 * a component can be enabled (default) or disabled by specifying the 'enabled' value
	 * in the configuration.
	 *
	 * If a configuration is specified with an ID that is the same as an existing
	 * component or configuration, the existing one will be replaced silently.
	 *
	 * The following is the configuration for two components:
	 * <pre>
	 * array(
	 *	 'db'=>array(
	 *		 'class'=>'CDbConnection',
	 *		 'connectionString'=>'sqlite:path/to/file.db',
	 *	 ),
	 *	 'cache'=>array(
	 *		 'class'=>'CDbCache',
	 *		 'connectionID'=>'db',
	 *		 'enabled'=>!YII_DEBUG,  // enable caching in non-debug mode
	 *	 ),
	 * )
	 * </pre>
	 *
	 * @param array $components application components(id=>component configuration or instances)
	 * @param boolean $merge whether to merge the new component configuration with the existing one.
	 * Defaults to true, meaning the previously registered component configuration of the same ID
	 * will be merged with the new configuration. If false, the existing configuration will be replaced completely.
	 */
	public function setComponents($components, $merge = true)
	{
		foreach ($components as $id => $component)
		{
			if ($component instanceof IApplicationComponent) {
				$this->setComponent($id, $component);
			}
			elseif (isset($this->_componentConfig[$id]) && $merge)
			{
				$this->_componentConfig[$id] = CMap::mergeArray($this->_componentConfig[$id], $component);
			}
			else
			{
				$this->_componentConfig[$id] = $component;
			}
		}
	}

	/**
	 * Configures the module with the specified configuration.
	 * @param array $config the configuration array
	 */
	public function configure($config)
	{
		if (is_array($config)) {
			foreach ($config as $key => $value)
			{
				$this->$key = $value;
			}
		}
	}

	/**
	 * Loads static application components.
	 */
	public function preloadComponents()
	{
		foreach ($this->preload as $id)
		{
			$this->getComponent($id);
		}
	}

	/**
	 * Preinitializes the module.
	 * This method is called at the beginning of the module constructor.
	 * You may override this method to do some customized preinitialization work.
	 * Note that at this moment, the module is not configured yet.
	 * @see init
	 */
	public function preinit()
	{
	}

	/**
	 * Initializes the module.
	 * This method is called at the end of the module constructor.
	 * Note that at this moment, the module has been configured, the behaviors
	 * have been attached and the application components have been registered.
	 * @see preinit
	 */
	public function init()
	{
	}
}
