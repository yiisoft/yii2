<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;
use yii\util\FileHelper;

/**
 * Widget is the base class for widgets.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Widget extends Component
{
	/**
	 * @var Widget|Controller the owner/creator of this widget. It could be either a widget or a controller.
	 */
	public $owner;
	/**
	 * @var string id of the widget.
	 */
	private $_id;
	/**
	 * @var integer a counter used to generate IDs for widgets.
	 */
	private static $_counter = 0;

	/**
	 * Constructor.
	 * @param Widget|Controller $owner owner/creator of this widget.
	 * @param array $config name-value pairs that will be used to initialize the object properties
	 */
	public function __construct($owner, $config = array())
	{
		$this->owner = $owner;
		parent::__construct($config);
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
	 * @param string $view the view name. Please refer to [[findViewFile()]] on how to specify a view name.
	 * @param array $params the parameters (name-value pairs) that should be made available in the view.
	 * @return string the rendering result.
	 * @throws InvalidParamException if the view file does not exist.
	 */
	public function render($view, $params = array())
	{
		$file = $this->findViewFile($view);
		return Yii::$app->getView()->render($this, $file, $params);
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
		return Yii::$app->getView()->render($this, $file, $params);
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
	 *
	 * The view name can be specified in one of the following formats:
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
	 *
	 * @param string $view the view name or the path alias of the view file.
	 * @return string the view file path. Note that the file may not exist.
	 * @throws InvalidParamException if the view file is an invalid path alias
	 */
	public function findViewFile($view)
	{
		if (strncmp($view, '@', 1) === 0) {
			// e.g. "@app/views/common"
			$file = Yii::getAlias($view);
		} elseif (strncmp($view, '/', 1) !== 0) {
			// e.g. "index"
			$file = $this->getViewPath() . DIRECTORY_SEPARATOR . $view;
		} elseif (strncmp($view, '//', 2) !== 0 && Yii::$app->controller !== null) {
			// e.g. "/site/index"
			$file = Yii::$app->controller->module->getViewPath() . DIRECTORY_SEPARATOR . ltrim($view, '/');
		} else {
			// e.g. "//layouts/main"
			$file = Yii::$app->getViewPath() . DIRECTORY_SEPARATOR . ltrim($view, '/');
		}
		if (FileHelper::getExtension($file) === '') {
			$file .= '.php';
		}
		return $file;
	}
}