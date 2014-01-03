<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;
use ReflectionClass;

/**
 * Widget is the base class for widgets.
 *
 * @property string $id ID of the widget.
 * @property \yii\web\View $view The view object that can be used to render views or view files. Note that the
 * type of this property differs in getter and setter. See [[getView()]] and [[setView()]] for details.
 * @property string $viewPath The directory containing the view files for this widget. This property is
 * read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Widget extends Component implements ViewContextInterface
{
	/**
	 * @var integer a counter used to generate [[id]] for widgets.
	 * @internal
	 */
	public static $counter = 0;
	/**
	 * @var string the prefix to the automatically generated widget IDs.
	 * @see [[getId()]]
	 */
	public static $autoIdPrefix = 'w';

	/**
	 * @var Widget[] the widgets that are currently being rendered (not ended). This property
	 * is maintained by [[begin()]] and [[end()]] methods.
	 * @internal
	 */
	public static $stack = [];


	/**
	 * Begins a widget.
	 * This method creates an instance of the calling class. It will apply the configuration
	 * to the created instance. A matching [[end()]] call should be called later.
	 * @param array $config name-value pairs that will be used to initialize the object properties
	 * @return static the newly created widget instance
	 */
	public static function begin($config = [])
	{
		$config['class'] = get_called_class();
		/** @var Widget $widget */
		$widget = Yii::createObject($config);
		self::$stack[] = $widget;
		return $widget;
	}

	/**
	 * Ends a widget.
	 * Note that the rendering result of the widget is directly echoed out.
	 * @return static the widget instance that is ended.
	 * @throws InvalidCallException if [[begin()]] and [[end()]] calls are not properly nested
	 */
	public static function end()
	{
		if (!empty(self::$stack)) {
			$widget = array_pop(self::$stack);
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
	 * @return string the rendering result of the widget.
	 */
	public static function widget($config = [])
	{
		ob_start();
		ob_implicit_flush(false);
		/** @var Widget $widget */
		$config['class'] = get_called_class();
		$widget = Yii::createObject($config);
		$widget->run();
		return ob_get_clean();
	}

	private $_id;

	/**
	 * Returns the ID of the widget.
	 * @param boolean $autoGenerate whether to generate an ID if it is not set previously
	 * @return string ID of the widget.
	 */
	public function getId($autoGenerate = true)
	{
		if ($autoGenerate && $this->_id === null) {
			$this->_id = self::$autoIdPrefix . self::$counter++;
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

	private $_view;

	/**
	 * Returns the view object that can be used to render views or view files.
	 * The [[render()]] and [[renderFile()]] methods will use
	 * this view object to implement the actual view rendering.
	 * If not set, it will default to the "view" application component.
	 * @return \yii\web\View the view object that can be used to render views or view files.
	 */
	public function getView()
	{
		if ($this->_view === null) {
			$this->_view = Yii::$app->getView();
		}
		return $this->_view;
	}

	/**
	 * Sets the view object to be used by this widget.
	 * @param View $view the view object that can be used to render views or view files.
	 */
	public function setView($view)
	{
		$this->_view = $view;
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
	public function render($view, $params = [])
	{
		return $this->getView()->render($view, $params, $this);
	}

	/**
	 * Renders a view file.
	 * @param string $file the view file to be rendered. This can be either a file path or a path alias.
	 * @param array $params the parameters (name-value pairs) that should be made available in the view.
	 * @return string the rendering result.
	 * @throws InvalidParamException if the view file does not exist.
	 */
	public function renderFile($file, $params = [])
	{
		return $this->getView()->renderFile($file, $params, $this);
	}

	/**
	 * Returns the directory containing the view files for this widget.
	 * The default implementation returns the 'views' subdirectory under the directory containing the widget class file.
	 * @return string the directory containing the view files for this widget.
	 */
	public function getViewPath()
	{
		$class = new ReflectionClass($this);
		return dirname($class->getFileName()) . DIRECTORY_SEPARATOR . 'views';
	}

	/**
	 * Finds the view file based on the given view name.
	 * File will be searched under [[viewPath]] directory.
	 * @param string $view the view name.
	 * @return string the view file path. Note that the file may not exist.
	 */
	public function findViewFile($view)
	{
		return $this->getViewPath() . DIRECTORY_SEPARATOR . $view;
	}
}
