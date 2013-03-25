<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;
use yii\base\Application;
use yii\helpers\FileHelper;

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
	public $context;
	/**
	 * @var mixed custom parameters that are shared among view templates.
	 */
	public $params;
	/**
	 * @var ViewRenderer|array the view renderer object or the configuration array for
	 * creating the view renderer. If not set, view files will be treated as normal PHP files.
	 */
	public $renderer;
	/**
	 * @var Theme|array the theme object or the configuration array for creating the theme.
	 * If not set, it means theming is not enabled.
	 */
	public $theme;
	/**
	 * @var array a list of named output clips. You can call [[beginClip()]] and [[endClip()]]
	 * to capture small fragments of a view. They can be later accessed at somewhere else
	 * through this property.
	 */
	public $clips;

	/**
	 * @var Widget[] the widgets that are currently not ended
	 */
	private $_widgetStack = array();


	/**
	 * Initializes the view component.
	 */
	public function init()
	{
		parent::init();
		if (is_array($this->renderer)) {
			$this->renderer = Yii::createObject($this->renderer);
		}
		if (is_array($this->theme)) {
			$this->theme = Yii::createObject($this->theme);
		}
	}

	/**
	 * Renders a view.
	 *
	 * This method will call [[findViewFile()]] to convert the view name into the corresponding view
	 * file path, and it will then call [[renderFile()]] to render the view.
	 *
	 * @param string $view the view name. Please refer to [[findViewFile()]] on how to specify this parameter.
	 * @param array $params the parameters (name-value pairs) that will be extracted and made available in the view file.
	 * @param object $context the context that the view should use for rendering the view. If null,
	 * existing [[context]] will be used.
	 * @return string the rendering result
	 * @throws InvalidParamException if the view cannot be resolved or the view file does not exist.
	 * @see renderFile
	 * @see findViewFile
	 */
	public function render($view, $params = array(), $context = null)
	{
		$viewFile = $this->findViewFile($context, $view);
		return $this->renderFile($viewFile, $params, $context);
	}

	/**
	 * Renders a view file.
	 *
	 * If [[theme]] is enabled (not null), it will try to render the themed version of the view file as long
	 * as it is available.
	 *
	 * The method will call [[FileHelper::localize()]] to localize the view file.
	 *
	 * If [[renderer]] is enabled (not null), the method will use it to render the view file.
	 * Otherwise, it will simply include the view file as a normal PHP file, capture its output and
	 * return it as a string.
	 *
	 * @param string $viewFile the view file. This can be either a file path or a path alias.
	 * @param array $params the parameters (name-value pairs) that will be extracted and made available in the view file.
	 * @param object $context the context that the view should use for rendering the view. If null,
	 * existing [[context]] will be used.
	 * @return string the rendering result
	 * @throws InvalidParamException if the view file does not exist
	 */
	public function renderFile($viewFile, $params = array(), $context = null)
	{
		$viewFile = Yii::getAlias($viewFile);
		if (is_file($viewFile)) {
			if ($this->theme !== null) {
				$viewFile = $this->theme->applyTo($viewFile);
			}
			$viewFile = FileHelper::localize($viewFile);
		} else {
			throw new InvalidParamException("The view file does not exist: $viewFile");
		}

		$oldContext = $this->context;
		if ($context !== null) {
			$this->context = $context;
		}

		if ($this->renderer !== null) {
			$output = $this->renderer->render($this, $viewFile, $params);
		} else {
			$output = $this->renderPhpFile($viewFile, $params);
		}

		$this->context = $oldContext;

		return $output;
	}

	/**
	 * Renders a view file as a PHP script.
	 *
	 * This method treats the view file as a PHP script and includes the file.
	 * It extracts the given parameters and makes them available in the view file.
	 * The method captures the output of the included view file and returns it as a string.
	 *
	 * This method should mainly be called by view renderer or [[renderFile()]].
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
	 * - relative path (e.g. "index"): the actual view file will be looked for under [[Controller::viewPath|viewPath]]
	 *   of the context object, assuming the context is either a [[Controller]] or a [[Widget]].
	 *
	 * If the view name does not contain a file extension, it will use the default one `.php`.
	 *
	 * @param object $context the view context object
	 * @param string $view the view name or the path alias of the view file.
	 * @return string the view file path. Note that the file may not exist.
	 * @throws InvalidParamException if the view file is an invalid path alias or the context cannot be
	 * used to determine the actual view file corresponding to the specified view.
	 */
	protected function findViewFile($context, $view)
	{
		if (strncmp($view, '@', 1) === 0) {
			// e.g. "@app/views/main"
			$file = Yii::getAlias($view);
		} elseif (strncmp($view, '//', 2) === 0) {
			// e.g. "//layouts/main"
			$file = Yii::$app->getViewPath() . DIRECTORY_SEPARATOR . ltrim($view, '/');
		} elseif (strncmp($view, '/', 1) === 0) {
			// e.g. "/site/index"
			$file = Yii::$app->controller->module->getViewPath() . DIRECTORY_SEPARATOR . ltrim($view, '/');
		} elseif ($context instanceof Controller || $context instanceof Widget) {
			/** @var $context Controller|Widget */
			$file = $context->getViewPath() . DIRECTORY_SEPARATOR . $view;
		} else {
			throw new InvalidParamException("Unable to resolve the view file for '$view'.");
		}

		return FileHelper::getExtension($file) === '' ? $file . '.php' : $file;
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
		return Yii::createObject($properties, $this->context);
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
	 * @throws InvalidCallException if [[beginWidget()]] and [[endWidget()]] calls are not properly nested
	 */
	public function endWidget()
	{
		$widget = array_pop($this->_widgetStack);
		if ($widget instanceof Widget) {
			$widget->run();
			return $widget;
		} else {
			throw new InvalidCallException("Unmatched beginWidget() and endWidget() calls.");
		}
	}

	/**
	 * Begins recording a clip.
	 * This method is a shortcut to beginning [[yii\widgets\Clip]]
	 * @param string $id the clip ID.
	 * @param boolean $renderInPlace whether to render the clip content in place.
	 * Defaults to false, meaning the captured clip will not be displayed.
	 * @return \yii\widgets\Clip the Clip widget instance
	 * @see \yii\widgets\Clip
	 */
	public function beginClip($id, $renderInPlace = false)
	{
		return $this->beginWidget('yii\widgets\Clip', array(
			'id' => $id,
			'renderInPlace' => $renderInPlace,
			'view' => $this,
		));
	}

	/**
	 * Ends recording a clip.
	 */
	public function endClip()
	{
		$this->endWidget();
	}

	/**
	 * Begins the rendering of content that is to be decorated by the specified view.
	 * @param string $view the name of the view that will be used to decorate the content enclosed by this widget.
	 * Please refer to [[View::findViewFile()]] on how to set this property.
	 * @param array $params the variables (name=>value) to be extracted and made available in the decorative view.
	 * @return \yii\widgets\ContentDecorator the ContentDecorator widget instance
	 * @see \yii\widgets\ContentDecorator
	 */
	public function beginContent($view, $params = array())
	{
		return $this->beginWidget('yii\widgets\ContentDecorator', array(
			'view' => $this,
			'viewName' => $view,
			'params' => $params,
		));
	}

	/**
	 * Ends the rendering of content.
	 */
	public function endContent()
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
	 * @param array $properties initial property values for [[\yii\widgets\FragmentCache]]
	 * @return boolean whether you should generate the content for caching.
	 * False if the cached version is available.
	 */
	public function beginCache($id, $properties = array())
	{
		$properties['id'] = $id;
		/** @var $cache \yii\widgets\FragmentCache */
		$cache = $this->beginWidget('yii\widgets\OutputCache', $properties);
		if ($cache->getCachedContent() !== false) {
			$this->endCache();
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Ends fragment caching.
	 */
	public function endCache()
	{
		$this->endWidget();
	}
}