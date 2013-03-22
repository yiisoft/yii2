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
		return Yii::$app->getView()->render($view, $params, $this);
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
		return Yii::$app->getView()->renderFile($file, $params, $this);
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
}