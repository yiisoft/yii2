<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;
use yii\util\FileHelper;
use yii\base\Application;

/**
 * View represents a view object in the MVC pattern.
 * 
 * View provides a set of methods (e.g. [[render()]]) for rendering purpose.
 * 
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class View extends Component
{
	/**
	 * @var object the object that owns this view. This can be a controller, a widget, or any other object.
	 */
	public $owner;
	/**
	 * @var string the layout to be applied when [[render()]] or [[renderContent()]] is called.
	 * If not set, it will use the [[Module::layout]] of the currently active module.
	 */
	public $layout;
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
	public $enableI18N = true;
	/**
	 * @var boolean whether to theme the view when possible. Defaults to true.
	 * Note that theming will be disabled if [[Application::theme]] is not set.
	 */
	public $enableTheme = true;
	/**
	 * @var mixed custom parameters that are available in the view template
	 */
	public $params;

	/**
	 * @var Widget[] the widgets that are currently not ended
	 */
	private  $_widgetStack = array();

	/**
	 * Constructor.
	 * @param object $owner the owner of this view. This usually is a controller or a widget.
	 * @param array $config name-value pairs that will be used to initialize the object properties
	 */
	public function __construct($owner, $config = array())
	{
		$this->owner = $owner;
		parent::__construct($config);
	}

	/**
	 * Renders a view within a layout.
	 * This method is similar to [[renderPartial()]] except that if a layout is available,
	 * this method will embed the view result into the layout and then return it.
	 * @param string $view the view to be rendered. Please refer to [[findViewFile()]] on possible formats of the view name.
	 * @param array $params the parameters that should be made available in the view. The PHP function `extract()`
	 * will be called on this variable to extract the variables from this parameter.
	 * @return string the rendering result
	 * @throws InvalidConfigException if the view file or layout file cannot be found
	 * @see findViewFile()
	 * @see findLayoutFile()
	 */
	public function render($view, $params = array())
	{
		$content = $this->renderPartial($view, $params);
		return $this->renderContent($content);
	}

	/**
	 * Renders a text content within a layout.
	 * The layout being used is resolved by [[findLayout()]].
	 * If no layout is available, the content will be returned back.
	 * @param string $content the content to be rendered
	 * @return string the rendering result
	 * @throws InvalidConfigException if the layout file cannot be found
	 * @see findLayoutFile()
	 */
	public function renderContent($content)
	{
		$layoutFile = $this->findLayoutFile();
		if ($layoutFile !== false) {
			return $this->renderFile($layoutFile, array('content' => $content));
		} else {
			return $content;
		}
	}

	/**
	 * Renders a view.
	 *
	 * The method first finds the actual view file corresponding to the specified view.
	 * It then calls [[renderFile()]] to render the view file. The rendering result is returned
	 * as a string. If the view file does not exist, an exception will be thrown.
	 *
	 * @param string $view the view to be rendered. Please refer to [[findViewFile()]] on possible formats of the view name.
	 * @param array $params the parameters that should be made available in the view. The PHP function `extract()`
	 * will be called on this variable to extract the variables from this parameter.
	 * @return string the rendering result
	 * @throws InvalidParamException if the view file cannot be found
	 * @see findViewFile()
	 */
	public function renderPartial($view, $params = array())
	{
		$file = $this->findViewFile($view);
		if ($file !== false) {
			return $this->renderFile($file, $params);
		} else {
			throw new InvalidParamException("Unable to find the view file for view '$view'.");
		}
	}

	/**
	 * Renders a view file.
	 *
	 * If a [[ViewRenderer|view renderer]] is installed, this method will try to use the view renderer
	 * to render the view file. Otherwise, it will simply include the view file, capture its output
	 * and return it as a string.
	 *
	 * @param string $file the view file.
	 * @param array $params the parameters (name-value pairs) that will be extracted and made available in the view file.
	 * @return string the rendering result
	 */
	public function renderFile($file, $params = array())
	{
		$renderer = Yii::$app->getViewRenderer();
		if ($renderer !== null) {
			return $renderer->render($this, $file, $params);
		} else {
			return $this->renderPhpFile($file, $params);
		}
	}

	/**
	 * Renders a view file as a PHP script.
	 *
	 * This method treats the view file as a PHP script and includes the file.
	 * It extracts the given parameters and makes them available in the view file.
	 * The method captures the output of the included view file and returns it as a string.
	 *
	 * @param string $_file_ the view file.
	 * @param array $_params_ the parameters (name-value pairs) that will be extracted and made available in the view file.
	 * @return string the rendering result
	 */
	public function renderPhpFile($_file_, $_params_ = array())
	{
		ob_start();
		ob_implicit_flush(false);
		extract($_params_, EXTR_OVERWRITE);
		require($_file_);
		return ob_get_clean();
	}

	/**
	 * Creates a widget.
	 * This method will use [[Yii::createObject()]] to create the widget.
	 * @param string $class the widget class name or path alias
	 * @param array $properties the initial property values of the widget.
	 * @return Widget the newly created widget instance
	 */
	public function createWidget($class, $properties = array())
	{
		$properties['class'] = $class;
		return Yii::createObject($properties, $this->owner);
	}

	/**
	 * Creates and runs a widget.
	 * Compared with [[createWidget()]], this method does one more thing: it will
	 * run the widget after it is created.
	 * @param string $class the widget class name or path alias
	 * @param array $properties the initial property values of the widget.
	 * @param boolean $captureOutput whether to capture the output of the widget and return it as a string
	 * @return string|Widget if $captureOutput is true, the output of the widget will be returned;
	 * otherwise the widget object will be returned.
	 */
	public function widget($class, $properties = array(), $captureOutput = false)
	{
		if ($captureOutput) {
			ob_start();
			ob_implicit_flush(false);
			$widget = $this->createWidget($class, $properties);
			$widget->run();
			return ob_get_clean();
		} else {
			$widget = $this->createWidget($class, $properties);
			$widget->run();
			return $widget;
		}
	}

	/**
	 * Begins a widget.
	 * This method is similar to [[createWidget()]] except that it will expect a matching
	 * [[endWidget()]] call after this.
	 * @param string $class the widget class name or path alias
	 * @param array $properties the initial property values of the widget.
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
		$widget = array_pop($this->_widgetStack);
		if ($widget instanceof Widget) {
			$widget->run();
			return $widget;
		} else {
			throw new Exception("Unmatched beginWidget() and endWidget() calls.");
		}
	}
//
//	/**
//	 * Begins recording a clip.
//	 * This method is a shortcut to beginning [[yii\widgets\Clip]]
//	 * @param string $id the clip ID.
//	 * @param array $properties initial property values for [[yii\widgets\Clip]]
//	 */
//	public function beginClip($id, $properties = array())
//	{
//		$properties['id'] = $id;
//		$this->beginWidget('yii\widgets\Clip', $properties);
//	}
//
//	/**
//	 * Ends recording a clip.
//	 */
//	public function endClip()
//	{
//		$this->endWidget();
//	}
//
//	/**
//	 * Begins fragment caching.
//	 * This method will display cached content if it is available.
//	 * If not, it will start caching and would expect an [[endCache()]]
//	 * call to end the cache and save the content into cache.
//	 * A typical usage of fragment caching is as follows,
//	 *
//	 * ~~~
//	 * if($this->beginCache($id)) {
//	 *     // ...generate content here
//	 *     $this->endCache();
//	 * }
//	 * ~~~
//	 *
//	 * @param string $id a unique ID identifying the fragment to be cached.
//	 * @param array $properties initial property values for [[yii\widgets\OutputCache]]
//	 * @return boolean whether we need to generate content for caching. False if cached version is available.
//	 * @see endCache
//	 */
//	public function beginCache($id, $properties = array())
//	{
//		$properties['id'] = $id;
//		$cache = $this->beginWidget('yii\widgets\OutputCache', $properties);
//		if ($cache->getIsContentCached()) {
//			$this->endCache();
//			return false;
//		} else {
//			return true;
//		}
//	}
//
//	/**
//	 * Ends fragment caching.
//	 * This is an alias to [[endWidget()]]
//	 * @see beginCache
//	 */
//	public function endCache()
//	{
//		$this->endWidget();
//	}
//
//	/**
//	 * Begins the rendering of content that is to be decorated by the specified view.
//	 * @param mixed $view the name of the view that will be used to decorate the content. The actual view script
//	 * is resolved via {@link getViewFile}. If this parameter is null (default),
//	 * the default layout will be used as the decorative view.
//	 * Note that if the current controller does not belong to
//	 * any module, the default layout refers to the application's {@link CWebApplication::layout default layout};
//	 * If the controller belongs to a module, the default layout refers to the module's
//	 * {@link CWebModule::layout default layout}.
//	 * @param array $params the variables (name=>value) to be extracted and made available in the decorative view.
//	 * @see endContent
//	 * @see yii\widgets\ContentDecorator
//	 */
//	public function beginContent($view, $params = array())
//	{
//		$this->beginWidget('yii\widgets\ContentDecorator', array(
//			'view' => $view,
//			'params' => $params,
//		));
//	}
//
//	/**
//	 * Ends the rendering of content.
//	 * @see beginContent
//	 */
//	public function endContent()
//	{
//		$this->endWidget();
//	}

	/**
	 * Finds the view file based on the given view name.
	 *
	 * A view name can be specified in one of the following formats:
	 *
	 * - path alias (e.g. "@app/views/site/index");
	 * - absolute path within application (e.g. "//site/index"): the view name starts with double slashes.
	 *   The actual view file will be looked for under the [[Application::viewPath|view path]] of the application.
	 * - absolute path within module (e.g. "/site/index"): the view name starts with a single slash.
	 *   The actual view file will be looked for under the [[Module::viewPath|view path]] of the currently
	 *   active module.
	 * - relative path (e.g. "index"): the actual view file will be looked for under the [[owner]]'s view path.
	 *   If [[owner]] is a widget or a controller, its view path is given by their `viewPath` property.
	 *   If [[owner]] is an object of any other type, its view path is the `view` sub-directory of the directory
	 *   containing the owner class file.
	 *
	 * If the view name does not contain a file extension, it will default to `.php`.
	 *
	 * If [[enableTheme]] is true and there is an active application them, the method will also
	 * attempt to use a themed version of the view file, when available.
	 *
	 * And if [[enableI18N]] is true, the method will attempt to use a translated version of the view file,
	 * when available.
	 *
	 * @param string $view the view name or path alias. If the view name does not specify
	 * the view file extension name, it will use `.php` as the extension name.
	 * @return string the view file path if it exists. False if the view file cannot be found.
	 * @throws InvalidConfigException if the view file does not exist
	 */
	public function findViewFile($view)
	{
		if (FileHelper::getExtension($view) === '') {
			$view .= '.php';
		}
		if (strncmp($view, '@', 1) === 0) {
			// e.g. "@app/views/common"
			if (($file = Yii::getAlias($view)) === false) {
				throw new InvalidConfigException("Invalid path alias: $view");
			}
		} elseif (strncmp($view, '/', 1) !== 0) {
			// e.g. "index"
			if ($this->owner instanceof Controller || $this->owner instanceof Widget) {
				$file = $this->owner->getViewPath() . DIRECTORY_SEPARATOR . $view;
			} elseif ($this->owner !== null) {
				$class = new \ReflectionClass($this->owner);
				$file = dirname($class->getFileName()) . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $view;
			} else {
				$file = Yii::$app->getViewPath() . DIRECTORY_SEPARATOR . $view;
			}
		} elseif (strncmp($view, '//', 2) !== 0 && Yii::$app->controller !== null) {
			// e.g. "/site/index"
			$file = Yii::$app->controller->module->getViewPath() . DIRECTORY_SEPARATOR . ltrim($view, '/');
		} else {
			// e.g. "//layouts/main"
			$file = Yii::$app->getViewPath() . DIRECTORY_SEPARATOR . ltrim($view, '/');
		}

		if (is_file($file)) {
			if ($this->enableTheme && ($theme = Yii::$app->getTheme()) !== null) {
				$file = $theme->apply($file);
			}
			return $this->enableI18N ? FileHelper::localize($file, $this->language, $this->sourceLanguage) : $file;
		} else {
			throw new InvalidConfigException("View file for view '$view' does not exist: $file");
		}
	}

	/**
	 * Finds the layout file that can be applied to the view.
	 *
	 * The applicable layout is resolved according to the following rules:
	 *
	 * - If [[layout]] is specified as a string, use it as the layout name and search for the layout file
	 *   under the layout path of the currently active module;
	 * - If [[layout]] is null and [[owner]] is a controller:
	 *      * If the controller's [[Controller::layout|layout]] is a string, use it as the layout name
	 *        and search for the layout file under the layout path of the parent module of the controller;
	 *      * If the controller's [[Controller::layout|layout]] is null, look through its ancestor modules
	 *        and find the first one whose [[Module::layout|layout]] is not null. Use the layout specified
	 *        by that module;
	 * - Returns false for all other cases.
	 *
	 * Like view names, a layout name can take several formats:
	 *
	 * - path alias (e.g. "@app/views/layouts/main");
	 * - absolute path (e.g. "/main"): the layout name starts with a slash. The actual layout file will be
	 *   looked for under the [[Application::layoutPath|layout path]] of the application;
	 * - relative path (e.g. "main"): the actual layout layout file will be looked for under the
	 *   [[Module::viewPath|view path]] of the context module determined by the above layout resolution process.
	 *
	 * If the layout name does not contain a file extension, it will default to `.php`.
	 *
	 * If [[enableTheme]] is true and there is an active application them, the method will also
	 * attempt to use a themed version of the layout file, when available.
	 *
	 * And if [[enableI18N]] is true, the method will attempt to use a translated version of the layout file,
	 * when available.
	 *
	 * @return string|boolean the layout file path, or false if layout is not needed.
	 * @throws InvalidConfigException if the layout file cannot be found
	 */
	public function findLayoutFile()
	{
		/** @var $module Module */
		if (is_string($this->layout)) {
			if (Yii::$app->controller) {
				$module = Yii::$app->controller->module;
			} else {
				$module = Yii::$app;
			}
			$view = $this->layout;
		} elseif ($this->owner instanceof Controller) {
			if (is_string($this->owner->layout)) {
				$module = $this->owner->module;
				$view = $this->owner->layout;
			} elseif ($this->owner->layout === null) {
				$module = $this->owner->module;
				while ($module !== null && $module->layout === null) {
					$module = $module->module;
				}
				if ($module !== null && is_string($module->layout)) {
					$view = $module->layout;
				}
			}
		}

		if (!isset($view)) {
			return false;
		}

		if (FileHelper::getExtension($view) === '') {
			$view .= '.php';
		}
		if (strncmp($view, '@', 1) === 0) {
			if (($file = Yii::getAlias($view)) === false) {
				throw new InvalidConfigException("Invalid path alias: $view");
			}
		} elseif (strncmp($view, '/', 1) === 0) {
			$file = Yii::$app->getLayoutPath() . DIRECTORY_SEPARATOR . $view;
		} else {
			$file = $module->getLayoutPath() . DIRECTORY_SEPARATOR . $view;
		}

		if (is_file($file)) {
			if ($this->enableTheme && ($theme = Yii::$app->getTheme()) !== null) {
				$file = $theme->apply($file);
			}
			return $this->enableI18N ? FileHelper::localize($file, $this->language, $this->sourceLanguage) : $file;
		} else {
			throw new InvalidConfigException("Layout file for layout '$view' does not exist: $file");
		}
	}
}