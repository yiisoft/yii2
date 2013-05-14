<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;

/**
 * Widget is the base class for widgets.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Widget extends Component
{
	/**
	 * @var View the view object that this widget is associated with.
	 * The widget will use this view object to register any needed assets.
	 * This property is also required by [[render()]] and [[renderFile()]].
	 */
	public $view;
	/**
	 * @var string id of the widget.
	 */
	private $_id;
	/**
	 * @var integer a counter used to generate [[id]] for widgets.
	 * @internal
	 */
	public static $_counter = 0;
	/**
	 * @var Widget[] the widgets that are currently being rendered (not ended). This property
	 * is maintained by [[begin()]] and [[end()]] methods.
	 * @internal
	 */
	public static $_stack = array();

	/**
	 * Constructor.
	 * @param array $config name-value pairs that will be used to initialize the object properties
	 *
	 * Note that you can specify the view object that this widget is associated with by setting 'view' key of config
	 * array. The widget will use this view object to register any needed assets and perform [[render()]]
	 * and [[renderFile()]]. By default widget uses current active controller's view object.
	 */
	public function __construct($config = array())
	{
		if(!isset($config['view'])) {
			$this->view = \Yii::$app->controller->getView();
		}
		parent::__construct($config);
	}

	/**
	 * Begins a widget.
	 * This method creates an instance of the calling class. It will apply the configuration
	 * to the created instance. A matching [[end()]] call should be called later.
	 * @param array $config name-value pairs that will be used to initialize the object properties
	 *
	 * Note that you can specify the view object that this widget is associated with by setting 'view' key of config
	 * array. The widget will use this view object to register any needed assets and perform [[render()]]
	 * and [[renderFile()]]. By default widget uses current active controller's view object.
	 *
	 * @return Widget the newly created widget instance
	 */
	public static function begin($config = array())
	{
		$config['class'] = get_called_class();
		/** @var Widget $widget */
		$widget = Yii::createObject($config);
		self::$_stack[] = $widget;
		return $widget;
	}

	/**
	 * Ends a widget.
	 * Note that the rendering result of the widget is directly echoed out.
	 * @return Widget the widget instance that is ended.
	 * @throws InvalidCallException if [[begin()]] and [[end()]] calls are not properly nested
	 */
	public static function end()
	{
		if (!empty(self::$_stack)) {
			$widget = array_pop(self::$_stack);
			if (get_class($widget) === get_called_class()) {
				$widget->run();
				return $widget;
			} else {
				throw new InvalidCallException("Expecting end() of " . get_class($widget) . ", found " . get_called_class());
			}
		} else {
			throw new InvalidCallException("Unexpected " . get_called_class() . '::end() call. A matching begin() is not found.');
		}
	}

	/**
	 * Creates a widget instance and runs it.
	 * The widget rendering result is returned by this method.
	 * @param array $config name-value pairs that will be used to initialize the object properties
	 *
	 * Note that you can specify the view object that this widget is associated with by setting 'view' key of config
	 * array. The widget will use this view object to register any needed assets and perform [[render()]]
	 * and [[renderFile()]]. By default widget uses current active controller's view object.
	 *
	 * @return string the rendering result of the widget.
	 */
	public static function widget($config = array())
	{
		ob_start();
		ob_implicit_flush(false);
		/** @var Widget $widget */
		$config['class'] = get_called_class();
		$widget = Yii::createObject($config, $view);
		$widget->run();
		return ob_get_clean();
	}

	/**
	 * Returns the ID of the widget.
	 * @param boolean $autoGenerate whether to generate an ID if it is not set previously
	 * @return string ID of the widget.
	 */
	public function getId($autoGenerate = true)
	{
		if ($autoGenerate && $this->_id === null) {
			$this->_id = 'w' . self::$_counter++;
		}
		return $this->_id;
	}

	/**
	 * Sets the ID of the widget.
	 * @param string $value id of the widget.
	 */
	public function setId($value)
	{
		$this->_id = $value;
	}

	/**
	 * Executes the widget.
	 */
	public function run()
	{
	}

	/**
	 * Renders a view.
	 * The view to be rendered can be specified in one of the following formats:
	 *
	 * - path alias (e.g. "@app/views/site/index");
	 * - absolute path within application (e.g. "//site/index"): the view name starts with double slashes.
	 *   The actual view file will be looked for under the [[Application::viewPath|view path]] of the application.
	 * - absolute path within module (e.g. "/site/index"): the view name starts with a single slash.
	 *   The actual view file will be looked for under the [[Module::viewPath|view path]] of the currently
	 *   active module.
	 * - relative path (e.g. "index"): the actual view file will be looked for under [[viewPath]].
	 *
	 * If the view name does not contain a file extension, it will use the default one `.php`.

	 * @param string $view the view name. Please refer to [[findViewFile()]] on how to specify a view name.
	 * @param array $params the parameters (name-value pairs) that should be made available in the view.
	 * @return string the rendering result.
	 * @throws InvalidParamException if the view file does not exist.
	 */
	public function render($view, $params = array())
	{
		$viewFile = $this->findViewFile($view);
		return $this->view->renderFile($viewFile, $params, $this);
	}

	/**
	 * Renders a view file.
	 * @param string $file the view file to be rendered. This can be either a file path or a path alias.
	 * @param array $params the parameters (name-value pairs) that should be made available in the view.
	 * @return string the rendering result.
	 * @throws InvalidParamException if the view file does not exist.
	 */
	public function renderFile($file, $params = array())
	{
		return $this->view->renderFile($file, $params, $this);
	}

	/**
	 * Returns the directory containing the view files for this widget.
	 * The default implementation returns the 'views' subdirectory under the directory containing the widget class file.
	 * @return string the directory containing the view files for this widget.
	 */
	public function getViewPath()
	{
		$className = get_class($this);
		$class = new \ReflectionClass($className);
		return dirname($class->getFileName()) . DIRECTORY_SEPARATOR . 'views';
	}

	/**
	 * Finds the view file based on the given view name.
	 * @param string $view the view name or the path alias of the view file. Please refer to [[render()]]
	 * on how to specify this parameter.
	 * @return string the view file path. Note that the file may not exist.
	 */
	protected function findViewFile($view)
	{
		if (strncmp($view, '@', 1) === 0) {
			// e.g. "@app/views/main"
			$file = Yii::getAlias($view);
		} elseif (strncmp($view, '//', 2) === 0) {
			// e.g. "//layouts/main"
			$file = Yii::$app->getViewPath() . DIRECTORY_SEPARATOR . ltrim($view, '/');
		} elseif (strncmp($view, '/', 1) === 0 && Yii::$app->controller !== null) {
			// e.g. "/site/index"
			$file = Yii::$app->controller->module->getViewPath() . DIRECTORY_SEPARATOR . ltrim($view, '/');
		} else {
			$file = $this->getViewPath() . DIRECTORY_SEPARATOR . ltrim($view, '/');
		}

		return pathinfo($file, PATHINFO_EXTENSION) === '' ? $file . '.php' : $file;
	}
}
