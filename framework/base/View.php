<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;
use yii\base\Application;
use yii\util\FileHelper;

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
	 * Renders a view file under a context with an optional layout.
	 *
	 * This method is similar to [[renderFile()]] except that it will update [[context]]
	 * with the provided $context parameter. It will also apply layout to the rendering result
	 * of the view file if $layoutFile is given.
	 *
	 * Theming and localization will be performed for the view file and the layout file, if possible.
	 *
	 * @param object $context the context object for rendering the file. This could be a controller, a widget,
	 * or any other object that serves as the rendering context of the view file. In the view file,
	 * it can be accessed through the [[context]] property.
	 * @param string $viewFile the view file. This can be a file path or a path alias.
	 * @param array $params the parameters (name-value pairs) that will be extracted and made available in the view file.
	 * @param string|boolean $layoutFile the layout file. This can be a file path or a path alias.
	 * If it is false, it means no layout should be applied.
	 * @return string the rendering result
	 * @throws InvalidParamException if the view file or the layout file does not exist.
	 */
	public function render($context, $viewFile, $params = array(), $layoutFile = false)
	{
		$oldContext = $this->context;
		$this->context = $context;

		$content = $this->renderFile($viewFile, $params);

		if ($layoutFile !== false) {
			$content = $this->renderFile($layoutFile, array('content' => $content));
		}

		$this->context = $oldContext;

		return $content;
	}

	/**
	 * Renders a view file.
	 *
	 * This method renders the specified view file under the existing [[context]].
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
	 * @param string $viewFile the view file. This can be a file path or a path alias.
	 * @param array $params the parameters (name-value pairs) that will be extracted and made available in the view file.
	 * @return string the rendering result
	 * @throws InvalidParamException if the view file does not exist
	 */
	public function renderFile($viewFile, $params = array())
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

		if ($this->renderer !== null) {
			return $this->renderer->render($this, $viewFile, $params);
		} else {
			return $this->renderPhpFile($viewFile, $params);
		}
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
}