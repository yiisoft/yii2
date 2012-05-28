<?php
/**
 * Widget class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * Widget is the base class for widgets.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Widget extends Component implements Initable
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
	 */
	public function __construct($owner)
	{
		$this->owner = $owner;
	}

	/**
	 * Returns the ID of the widget.
	 * @param boolean $autoGenerate whether to generate an ID if it is not set previously
	 * @return string ID of the widget.
	 */
	public function getId($autoGenerate = true)
	{
		if ($autoGenerate && $this->_id === null) {
			$this->_id = 'yw' . self::$_counter++;
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
	 * Initializes the widget.
	 */
	public function init()
	{
	}

	/**
	 * Executes the widget.
	 * @return string the rendering result of the widget
	 */
	public function run()
	{
	}

	/**
	 * Renders a view.
	 *
	 * The method first finds the actual view file corresponding to the specified view.
	 * It then calls [[renderFile()]] to render the view file. The rendering result is returned
	 * as a string. If the view file does not exist, an exception will be thrown.
	 *
	 * To determine which view file should be rendered, the method calls [[findViewFile()]] which
	 * will search in the directories as specified by [[basePath]].
	 *
	 * View name can be a path alias representing an absolute file path (e.g. `@app/views/layout/index`),
	 * or a path relative to [[basePath]]. The file suffix is optional and defaults to `.php` if not given
	 * in the view name.
	 *
	 * @param string $view the view to be rendered. This can be either a path alias or a path relative to [[basePath]].
	 * @param array $params the parameters that should be made available in the view. The PHP function `extract()`
	 * will be called on this variable to extract the variables from this parameter.
	 * @return string the rendering result
	 * @throws Exception if the view file cannot be found
	 */
	public function render($view, $params = array())
	{
		return $this->createView()->renderPartial($view, $params);
	}

	/**
	 * @return View
	 */
	public function createView()
	{
		return new View($this);
	}
}