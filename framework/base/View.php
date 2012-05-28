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
use yii\base\Application;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class View extends Component
{
	/**
	 * @var Controller|Widget|Object the context under which this view is being rendered
	 */
	public $context;
	/**
	 * @var string|array the directories where the view file should be looked for when a *relative* view name is given.
	 * This can be either a string representing a single directory, or an array representing multiple directories.
	 * If the latter, the view file will be looked for in the directories in the order they are specified.
	 * Path aliases can be used. If this property is not set, relative view names should be treated as absolute ones.
	 * @see roothPath
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
	 * @var boolean whether to localize the view when possible. Defaults to true.
	 * Note that when this is true, if a localized view cannot be found, the original view will be rendered.
	 * No error will be reported.
	 */
	public $localizeView = true;
	/**
	 * @var boolean whether to theme the view when possible. Defaults to true.
	 * Note that theming will be disabled if [[Application::theme]] is null.
	 */
	public $themeView = true;
	/**
	 * @var mixed custom parameters that are available in the view template
	 */
	public $params;

	/**
	 * Constructor.
	 * @param Controller|Widget|Object $context the context under which this view is being rendered (e.g. controller, widget)
	 */
	public function __construct($context = null)
	{
		$this->context = $context;
	}

	public function render($view, $params = array())
	{
		$content = $this->renderPartial($view, $params);
		return $this->renderText($content);
	}

	public function renderText($text)
	{
		$layoutFile = $this->findLayoutFile();
		if ($layoutFile !== false) {
			return $this->renderFile($layoutFile, array('content' => $text));
		} else {
			return $text;
		}
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
	public function renderPartial($view, $params = array())
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
		return \Yii::createObject($properties, $this->context);
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
		if (($extension = FileHelper::getExtension($view)) === '') {
			$view .= '.php';
		}
		if (strncmp($view, '@', 1) === 0) {
			$file = \Yii::getAlias($view);
		} elseif (strncmp($view, '/', 1) !== 0) {
			$file = $this->findRelativeViewFile($view);
		} else {
			$file = $this->findAbsoluteViewFile($view);
		}

		if ($file === false || !is_file($file)) {
			return false;
		} elseif ($this->localizeView) {
			return FileHelper::localize($file, $this->language, $this->sourceLanguage);
		} else {
			return $file;
		}
	}

	/**
	 * Finds the view file corresponding to the given relative view name.
	 * The method will look for the view file under a set of directories returned by [[resolveBasePath()]].
	 * If no base path is given, the view will be treated as an absolute view and the result of
	 * [[findAbsoluteViewFile()]] will be returned.
	 * @param string $view the relative view name
	 * @return string|boolean the view file path, or false if the view file cannot be found
	 */
	protected function findRelativeViewFile($view)
	{
		$paths = $this->resolveBasePath();
		if ($paths === array()) {
			return $this->findAbsoluteViewFile($view);
		}
		if ($this->themeView && $this->context !== null && ($theme = \Yii::$application->getTheme()) !== null) {
			array_unshift($paths, $theme->getViewPath($this->context));
		}
		foreach ($paths as $path) {
			$file = \Yii::getAlias($path . '/' . $view);
			if ($file !== false && is_file($file)) {
				return $file;
			}
		}
		return $paths === array() ? $this->findAbsoluteViewFile($view) : false;
	}

	/**
	 * Finds the view file corresponding to the given absolute view name.
	 * If the view name starts with double slashes `//`, the method will look for the view file
	 * under [[Application::getViewPath()]]. Otherwise, it will look for the view file under the
	 * view path of the currently active module.
	 * @param string $view the absolute view name
	 * @return string|boolean the view file path, or false if the view file cannot be found
	 */
	protected function findAbsoluteViewFile($view)
	{
		$app = \Yii::$application;
		if (strncmp($view, '//', 2) !== 0 && $app->controller !== null) {
			$module = $app->controller->module;
		} else {
			$module = $app;
		}
		if ($this->themeView && ($theme = $app->getTheme()) !== null) {
			$paths[] = $theme->getViewPath($module);
		}
		$paths[] = $module->getViewPath();
		$view = ltrim($view, '/');
		foreach ($paths as $path) {
			$file = \Yii::getAlias($path . '/' . $view);
			if ($file !== false && is_file($file)) {
				return $file;
			}
		}
		return false;
	}

	/**
	 * Resolves the base paths that will be used to determine view files for relative view names.
	 * The method resolves the base path using the following algorithm:
	 *
	 * - If [[basePath]] is not empty, it is returned;
	 * - If [[context]] is a controller, it will return the subdirectory named as
	 *   [[Controller::uniqueId]] under the controller's module view path;
	 * - If [[context]] is an object, it will return the `views` subdirectory under
	 *   the directory containing the object class file.
	 * - Otherwise, it will return false.
	 * @return array the base paths
	 */
	protected function resolveBasePath()
	{
		if (!empty($this->basePath)) {
			return is_array($this->basePath) ? $this->basePath : array($this->basePath);
		} elseif ($this->context instanceof Controller) {
			return $this->context->module->getViewPath() . '/' . $this->context->getUniqueId();
		} elseif ($this->context !== null) {
			$class = new \ReflectionClass($this->context);
			return array(dirname($class->getFileName()) . '/views');
		} else {
			return array();
		}
	}

	/**
	 * Finds the layout file for the current [[context]].
	 * The method will return false if [[context]] is not a controller.
	 * When [[context]] is a controller, the following algorithm is used to determine the layout file:
	 *
	 * - If `context.layout` is false, it will return false;
	 * - If `context.layout` is a string, it will look for the layout file under the [[Module::layoutPath|layout path]]
	 *   of the controller's parent module;
	 * - If `context.layout` is null, the following steps are taken to resolve the actual layout to be returned:
	 *      * Check the `layout` property of the parent module. If it is null, check the grand parent module and so on
	 *        until a non-null layout is encountered. Let's call this module the *effective module*.
	 *      * If the layout is null or false, it will return false;
	 *      * Otherwise, it will look for the layout file under the layout path of the effective module.
	 *
	 * The themed layout file will be returned if theme is enabled and the theme contains such a layout file.
	 *
	 * @return string|boolean the layout file path, or false if the context does not need layout.
	 * @throws Exception if the layout file cannot be found
	 */
	public function findLayoutFile()
	{
		if (!$this->context instanceof Controller || $this->context->layout === false) {
			return false;
		}
		$module = $this->context->module;
		while ($module !== null && $module->layout === null) {
			$module = $module->module;
		}
		if ($module === null || $module->layout === null || $module->layout === false) {
			return false;
		}

		$view = $module->layout;
		if (($extension = FileHelper::getExtension($view)) === '') {
			$view .= '.php';
		}
		if (strncmp($view, '@', 1) === 0) {
			$file = \Yii::getAlias($view);
		} elseif (strncmp($view, '/', 1) === 0) {
			$file = $this->findAbsoluteViewFile($view);
		} else {
			if ($this->themeView && ($theme = \Yii::$application->getTheme()) !== null) {
				$paths[] = $theme->getLayoutPath($module);
			}
			$paths[] = $module->getLayoutPath();
			$file = false;
			foreach ($paths as $path) {
				$f = \Yii::getAlias($path . '/' . $view);
				if ($f !== false && is_file($f)) {
					$file = $f;
					break;
				}
			}
		}
		if ($file === false || !is_file($file)) {
			throw new Exception("Unable to find the layout file for layout '{$module->layout}' (specified by " . get_class($module) . ")");
		} elseif ($this->localizeView) {
			return FileHelper::localize($file, $this->language, $this->sourceLanguage);
		} else {
			return $file;
		}
	}
}