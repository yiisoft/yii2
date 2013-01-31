<?php
/**
 * View class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
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
 *  
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class View extends Component
{
	/**
	 * @var string the layout to be applied when [[render()]] or [[renderContent()]] is called.
	 * If not set, it will use the value of [[Application::layout]].
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
	 * @var object the object that owns this view.
	 */
	private $_owner;
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
		$this->_owner = $owner;
		parent::__construct($config);
	}

	/**
	 * Returns the owner of this view.
	 * @return object the owner of this view.
	 */
	public function getOwner()
	{
		return $this->_owner;
	}

	/**
	 * Renders a view within the layout specified by [[owner]].
	 * This method is similar to [[renderPartial()]] except that if [[owner]] specifies a layout,
	 * this method will embed the view result into the layout and then return it.
	 * @param string $view the view to be rendered. This can be either a path alias or a path relative to [[searchPaths]].
	 * @param array $params the parameters that should be made available in the view. The PHP function `extract()`
	 * will be called on this variable to extract the variables from this parameter.
	 * @return string the rendering result
	 * @throws InvalidCallException if the view file cannot be found
	 * @see renderPartial()
	 */
	public function render($view, $params = array())
	{
		$content = $this->renderPartial($view, $params);
		return $this->renderContent($content);
	}

	/**
	 * Renders a text content within the layout specified by [[owner]].
	 * If the [[owner]] does not specify any layout, the content will be returned back.
	 * @param string $content the content to be rendered
	 * @return string the rendering result
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
	 * View name can be a path alias representing an absolute file path (e.g. `@application/views/layout/index`),
	 * or a path relative to [[searchPaths]]. The file suffix is optional and defaults to `.php` if not given
	 * in the view name.
	 *
	 * @param string $view the view to be rendered. This can be either a path alias or a path relative to [[searchPaths]].
	 * @param array $params the parameters that should be made available in the view. The PHP function `extract()`
	 * will be called on this variable to extract the variables from this parameter.
	 * @return string the rendering result
	 * @throws InvalidCallException if the view file cannot be found
	 * @see findViewFile()
	 */
	public function renderPartial($view, $params = array())
	{
		$file = $this->findViewFile($view);
		if ($file !== false) {
			return $this->renderFile($file, $params);
		} else {
			throw new InvalidCallException("Unable to find the view file for view '$view'.");
		}
	}

	/**
	 * Renders a view file.
	 * This method will extract the given parameters and include the view file.
	 * It captures the output of the included view file and returns it as a string.
	 * @param string $_file_ the view file.
	 * @param array $_params_ the parameters (name-value pairs) that will be extracted and made available in the view file.
	 * @return string the rendering result
	 */
	public function renderFile($_file_, $_params_ = array())
	{
		ob_start();
		ob_implicit_flush(false);
		extract($_params_, EXTR_OVERWRITE);
		require($_file_);
		return ob_get_clean();
	}

	public function createWidget($class, $properties = array())
	{
		$properties['class'] = $class;
		return Yii::createObject($properties, $this->_owner);
	}

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
		/** @var $widget Widget */
		if (($widget = array_pop($this->_widgetStack)) !== null) {
			$widget->run();
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
	 * Finds the view file based on the given view name.
	 *
	 * The rule for searching for the view file is as follows:
	 *
	 * - If the view name is given as a path alias, return the actual path corresponding to the alias;
	 * - If the view name does NOT start with a slash:
	 *       * If the view owner is a controller or widget, look for the view file under
	 *         the controller or widget's view path (see [[Controller::viewPath]] and [[Widget::viewPath]]);
	 *       * If the view owner is an object, look for the view file under the "views" sub-directory
	 *         of the directory containing the object class file;
	 *       * Otherwise, look for the view file under the application's [[Application::viewPath|view path]].
	 * - If the view name starts with a single slash, look for the view file under the currently active
	 *   module's [[Module::viewPath|view path]];
	 * - If the view name starts with double slashes, look for the view file under the application's
	 *   [[Application::viewPath|view path]].
	 *
	 * If [[enableTheme]] is true and there is an active application them, the method will also
	 * attempt to use a themed version of the view file, when available.
	 *
	 * @param string $view the view name or path alias. If the view name does not specify
	 * the view file extension name, it will use `.php` as the extension name.
	 * @return string|boolean the view file path if it exists. False if the view file cannot be found.
	 */
	public function findViewFile($view)
	{
		if (FileHelper::getExtension($view) === '') {
			$view .= '.php';
		}
		if (strncmp($view, '@', 1) === 0) {
			// e.g. "@application/views/common"
			$file = Yii::getAlias($view);
		} elseif (strncmp($view, '/', 1) !== 0) {
			// e.g. "index"
			if ($this->_owner instanceof Controller || $this->_owner instanceof Widget) {
				$path = $this->_owner->getViewPath() . DIRECTORY_SEPARATOR . $view;
			} elseif ($this->_owner !== null) {
				$class = new \ReflectionClass($this->_owner);
				$path = dirname($class->getFileName()) . DIRECTORY_SEPARATOR . 'views';
			} else {
				$path = Yii::$application->getViewPath();
			}
			$file = $path . DIRECTORY_SEPARATOR . $view;
		} elseif (strncmp($view, '//', 2) !== 0 && Yii::$application->controller !== null) {
			// e.g. "/site/index"
			$file = Yii::$application->controller->module->getViewPath() . DIRECTORY_SEPARATOR . ltrim($view, '/');
		} else {
			// e.g. "//layouts/main"
			$file = Yii::$application->getViewPath() . DIRECTORY_SEPARATOR . ltrim($view, '/');
		}

		if (is_file($file)) {
			if ($this->enableTheme && ($theme = Yii::$application->getTheme()) !== null) {
				$file = $theme->apply($file);
			}
			return $this->enableI18N ? FileHelper::localize($file, $this->language, $this->sourceLanguage) : $file;
		} else {
			return false;
		}
	}

	/**
	 * Finds the layout file for the current [[owner]].
	 * The method will return false if [[owner]] is not a controller.
	 * When [[owner]] is a controller, the following algorithm is used to determine the layout file:
	 *
	 * - If `content` is not a controller or if `owner.layout` is false, it will return false;
	 * - If `owner.layout` is a string, it will look for the layout file under the [[Module::layoutPath|layout path]]
	 *   of the controller's parent module;
	 * - If `owner.layout` is null, the following steps are taken to resolve the actual layout to be returned:
	 *      * Check the `layout` property of the parent module. If it is null, check the grand parent module and so on
	 *        until a non-null layout is encountered. Let's call this module the *effective module*.
	 *      * If the layout is null or false, it will return false;
	 *      * Otherwise, it will look for the layout file under the layout path of the effective module.
	 *
	 * The themed layout file will be returned if theme is enabled and the theme contains such a layout file.
	 *
	 * @return string|boolean the layout file path, or false if the owner does not need layout.
	 * @throws InvalidCallException if the layout file cannot be found
	 */
	public function findLayoutFile()
	{
		if ($this->layout === null || !$this->_owner instanceof Controller) {
			$layout = Yii::$application->layout;
		} elseif ($this->_owner->layout !== false) {

		}
		if (!$this->_owner instanceof Controller || $this->_owner->layout === false) {
			return false;
		}
		/** @var $module Module */
		$module = $this->_owner->module;
		while ($module !== null && $module->layout === null) {
			$module = $module->module;
		}
		if ($module === null || $module->layout === null || $module->layout === false) {
			return false;
		}

		$view = $module->layout;
		if (FileHelper::getExtension($view) === '') {
			$view .= '.php';
		}
		if (strncmp($view, '@', 1) === 0) {
			$file = Yii::getAlias($view);
		} elseif (strncmp($view, '/', 1) !== 0) {
			// e.g. "main"
			if ($this->_owner instanceof Controller || $this->_owner instanceof Widget) {
				$path = $this->_owner->getViewPath() . DIRECTORY_SEPARATOR . $view;
			} elseif ($this->_owner !== null) {
				$class = new \ReflectionClass($this->_owner);
				$path = dirname($class->getFileName()) . DIRECTORY_SEPARATOR . 'views';
			} else {
				$path = Yii::$application->getViewPath();
			}
			$file = $path . DIRECTORY_SEPARATOR . $view;
		} elseif (strncmp($view, '//', 2) !== 0 && Yii::$application->controller !== null) {
			// e.g. "/main"
			$file = Yii::$application->controller->module->getViewPath() . DIRECTORY_SEPARATOR . ltrim($view, '/');
		} else {
			// e.g. "//main"
			$file = Yii::$application->getViewPath() . DIRECTORY_SEPARATOR . ltrim($view, '/');
		} elseif (strncmp($view, '/', 1) === 0) {
			$file = $this->findAbsoluteViewFile($view);
		} else {
			$paths[] = $module->getLayoutPath();
			$file = false;
			foreach ($paths as $path) {
				$f = Yii::getAlias($path . '/' . $view);
				if ($f !== false && is_file($f)) {
					$file = $f;
					break;
				}
			}
		}
		if ($file === false || !is_file($file)) {
			throw new InvalidCallException("Unable to find the layout file for layout '{$module->layout}' (specified by " . get_class($module) . ")");
		} elseif ($this->enableI18N) {
			return FileHelper::localize($file, $this->language, $this->sourceLanguage);
		} else {
			return $file;
		}
	}
}