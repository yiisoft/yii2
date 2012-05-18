<?php
/**
 * View class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use yii\util\FileHelper;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class View extends Component
{
	/**
	 * @var Object the owner of this view
	 */
	public $owner;
	/**
	 * @var string|array the base path where the view file should be looked for using the specified view name.
	 * This can be either a string representing a single base path, or an array representing multiple base paths.
	 * If the latter, the view file will be looked for in the given base paths in the order they are specified.
	 * This property must be set before calling [[render()]].
	 */
	public $basePath;
	/**
	 * @var string the language that the view should be rendered in. If not set, it will use
	 * the value of [[Application::language]].
	 */
	public $language;
	/**
	 * @var string the language that the original view is in. If not set, it will use
	 * the value of [[Application::sourceLanguage]].
	 */
	public $sourceLanguage;
	/**
	 * @var mixed custom parameters that are available in the view template
	 */
	public $params;

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
		$file = $this->findViewFile($view);
		if ($file !== false) {
			return $this->renderFile($file, $params);
		} else {
			throw new Exception("Unable to find the view file for view '$view'.");
		}
	}

	/**
	 * Renders a view file.
	 * @param string $file the view file path
	 * @param array $params the parameters to be extracted and made available in the view file
	 * @return string the rendering result
	 */
	public function renderFile($file, $params = array())
	{
		return $this->renderFileInternal($file, $params);
	}

	public function createWidget($class, $properties = array())
	{
		$properties['class'] = $class;
		return \Yii::createObject($properties, $this->owner);
	}

	public function widget($class, $properties = array())
	{
		$widget = $this->createWidget($class, $properties);
		echo $widget->run();
		return $widget;
	}

	/**
	 * @var Widget[] the widgets that are currently not ended
	 */
	private $_widgetStack = array();

	/**
	 * Begins a widget.
	 * @param string $class the widget class
	 * @param array $properties the initial property values of the widget
	 * @return Widget the widget instance
	 */
	public function beginWidget($class, $properties = array())
	{
		$widget = $this->createWidget($class, $properties);
		$this->_widgetStack[] = $widget;
		return $widget;
	}

	/**
	 * Ends a widget.
	 * Note that the rendering result of the widget is directly echoed out.
	 * If you want to capture the rendering result of a widget, you may use
	 * [[createWidget()]] and [[Widget::run()]].
	 * @return Widget the widget instance
	 * @throws Exception if [[beginWidget()]] and [[endWidget()]] calls are not properly nested
	 */
	public function endWidget()
	{
		if (($widget = array_pop($this->_widgetStack)) !== null) {
			echo $widget->run();
			return $widget;
		} else {
			throw new Exception("Unmatched beginWidget() and endWidget() calls.");
		}
	}

	/**
	 * Begins recording a clip.
	 * This method is a shortcut to beginning [[yii\widgets\Clip]]
	 * @param string $id the clip ID.
	 * @param array $properties initial property values for [[yii\widgets\Clip]]
	 */
	public function beginClip($id, $properties = array())
	{
		$properties['id'] = $id;
		$this->beginWidget('yii\widgets\Clip', $properties);
	}

	/**
	 * Ends recording a clip.
	 * This method is an alias to {@link endWidget}.
	 */
	public function endClip()
	{
		$this->endWidget();
	}

	/**
	 * Begins fragment caching.
	 * This method will display cached content if it is available.
	 * If not, it will start caching and would expect an [[endCache()]]
	 * call to end the cache and save the content into cache.
	 * A typical usage of fragment caching is as follows,
	 *
	 * ~~~
	 * if($this->beginCache($id)) {
	 *     // ...generate content here
	 *     $this->endCache();
	 * }
	 * ~~~
	 *
	 * @param string $id a unique ID identifying the fragment to be cached.
	 * @param array $properties initial property values for [[yii\widgets\OutputCache]]
	 * @return boolean whether we need to generate content for caching. False if cached version is available.
	 * @see endCache
	 */
	public function beginCache($id, $properties = array())
	{
		$properties['id'] = $id;
		$cache = $this->beginWidget('yii\widgets\OutputCache', $properties);
		if ($cache->getIsContentCached()) {
			$this->endCache();
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Ends fragment caching.
	 * This is an alias to [[endWidget()]]
	 * @see beginCache
	 */
	public function endCache()
	{
		$this->endWidget();
	}

	/**
	 * Begins the rendering of content that is to be decorated by the specified view.
	 * @param mixed $view the name of the view that will be used to decorate the content. The actual view script
	 * is resolved via {@link getViewFile}. If this parameter is null (default),
	 * the default layout will be used as the decorative view.
	 * Note that if the current controller does not belong to
	 * any module, the default layout refers to the application's {@link CWebApplication::layout default layout};
	 * If the controller belongs to a module, the default layout refers to the module's
	 * {@link CWebModule::layout default layout}.
	 * @param array $params the variables (name=>value) to be extracted and made available in the decorative view.
	 * @see endContent
	 * @see yii\widgets\ContentDecorator
	 */
	public function beginContent($view, $params = array())
	{
		$this->beginWidget('yii\widgets\ContentDecorator', array(
			'view' => $view,
			'params' => $params,
		));
	}

	/**
	 * Ends the rendering of content.
	 * @see beginContent
	 */
	public function endContent()
	{
		$this->endWidget();
	}

	/**
	 * Renders a view file.
	 * This method will extract the given parameters and include the view file.
	 * It captures the output of the included view file and returns it as a string.
	 * @param string $_file_ the view file.
	 * @param array $_params_ the parameters (name-value pairs) that will be extracted and made available in the view file.
	 * @return string the rendering result
	 */
	protected function renderFileInternal($_file_, $_params_ = array())
	{
		ob_start();
		ob_implicit_flush(false);
		extract($_params_, EXTR_OVERWRITE);
		require($_file_);
		return ob_get_clean();
	}

	/**
	 * Finds the view file based on the given view name.
	 * @param string $view the view name or path alias. If the view name does not specify
	 * the view file extension name, it will use `.php` as the extension name.
	 * @return string|boolean the view file if it exists. False if the view file cannot be found.
	 */
	public function findViewFile($view)
	{
		$view = ltrim($view, '/');

		if (($extension = FileHelper::getExtension($view)) === '') {
			$view .= '.php';
		}
		if ($view[0] === '@') {
			$file = \Yii::getAlias($view);
		} elseif (!empty($this->basePath)) {
			$basePaths = is_array($this->basePath) ? $this->basePath : array($this->basePath);
			foreach ($basePaths as $basePath) {
				$file = $basePath . DIRECTORY_SEPARATOR . $view;
				if (is_file($file)) {
					break;
				}
			}
		}
		if (isset($file) && is_file($file)) {
			$file = FileHelper::localize($file, $this->language, $this->sourceLanguage);
			return is_file($file) ? $file : false;
		} else {
			return false;
		}
	}
}