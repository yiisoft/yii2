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
use yii\helpers\Html;

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
	 * @event ViewEvent an event that is triggered by [[beginPage()]].
	 */
	const EVENT_BEGIN_PAGE = 'beginPage';
	/**
	 * @event ViewEvent an event that is triggered by [[endPage()]].
	 */
	const EVENT_END_PAGE = 'endPage';
	/**
	 * @event ViewEvent an event that is triggered by [[renderFile()]] right before it renders a view file.
	 */
	const EVENT_BEFORE_RENDER = 'beforeRender';
	/**
	 * @event ViewEvent an event that is triggered by [[renderFile()]] right after it renders a view file.
	 */
	const EVENT_AFTER_RENDER = 'afterRender';

	/**
	 * The location of registered JavaScript code block or files.
	 * This means the location is in the head section.
	 */
	const POS_HEAD = 1;
	/**
	 * The location of registered JavaScript code block or files.
	 * This means the location is at the beginning of the body section.
	 */
	const POS_BEGIN = 2;
	/**
	 * The location of registered JavaScript code block or files.
	 * This means the location is at the end of the body section.
	 */
	const POS_END = 3;
	/**
	 * This is internally used as the placeholder for receiving the content registered for the head section.
	 */
	const PL_HEAD = '<![CDATA[YII-BLOCK-HEAD]]>';
	/**
	 * This is internally used as the placeholder for receiving the content registered for the beginning of the body section.
	 */
	const PL_BODY_BEGIN = '<![CDATA[YII-BLOCK-BODY-BEGIN]]>';
	/**
	 * This is internally used as the placeholder for receiving the content registered for the end of the body section.
	 */
	const PL_BODY_END = '<![CDATA[YII-BLOCK-BODY-END]]>';


	/**
	 * @var object the context under which the [[renderFile()]] method is being invoked.
	 * This can be a controller, a widget, or any other object.
	 */
	public $context;
	/**
	 * @var mixed custom parameters that are shared among view templates.
	 */
	public $params;
	/**
	 * @var array a list of available renderers indexed by their corresponding supported file extensions.
	 * Each renderer may be a view renderer object or the configuration for creating the renderer object.
	 * The default setting supports both Smarty and Twig (their corresponding file extension is "tpl"
	 * and "twig" respectively. Please refer to [[SmartyRenderer]] and [[TwigRenderer]] on how to install
	 * the needed libraries for these template engines.
	 *
	 * If no renderer is available for the given view file, the view file will be treated as a normal PHP
	 * and rendered via [[renderPhpFile()]].
	 */
	public $renderers = array(
		'tpl' => array(
			'class' => 'yii\renderers\SmartyRenderer',
		),
		'twig' => array(
			'class' => 'yii\renderers\TwigRenderer',
		),
	);
	/**
	 * @var Theme|array the theme object or the configuration array for creating the theme object.
	 * If not set, it means theming is not enabled.
	 */
	public $theme;
	/**
	 * @var array a list of named output blocks. The keys are the block names and the values
	 * are the corresponding block content. You can call [[beginBlock()]] and [[endBlock()]]
	 * to capture small fragments of a view. They can be later accessed somewhere else
	 * through this property.
	 */
	public $blocks;
	/**
	 * @var Widget[] the widgets that are currently being rendered (not ended). This property
	 * is maintained by [[beginWidget()]] and [[endWidget()]] methods. Do not modify it directly.
	 * @internal
	 */
	public $widgetStack = array();
	/**
	 * @var array a list of currently active fragment cache widgets. This property
	 * is used internally to implement the content caching feature. Do not modify it directly.
	 * @internal
	 */
	public $cacheStack = array();
	/**
	 * @var array a list of placeholders for embedding dynamic contents. This property
	 * is used internally to implement the content caching feature. Do not modify it directly.
	 * @internal
	 */
	public $dynamicPlaceholders = array();
	/**
	 * @var array the registered asset bundles. The keys are the bundle names, and the values
	 * are the corresponding [[AssetBundle]] objects.
	 * @see registerAssetBundle
	 */
	public $assetBundles;
	/**
	 * @var string the page title
	 */
	public $title;
	/**
	 * @var array the registered meta tags.
	 * @see registerMetaTag
	 */
	public $metaTags;
	/**
	 * @var array the registered link tags.
	 * @see registerLinkTag
	 */
	public $linkTags;
	/**
	 * @var array the registered CSS code blocks.
	 * @see registerCss
	 */
	public $css;
	/**
	 * @var array the registered CSS files.
	 * @see registerCssFile
	 */
	public $cssFiles;
	/**
	 * @var array the registered JS code blocks
	 * @see registerJs
	 */
	public $js;
	/**
	 * @var array the registered JS files.
	 * @see registerJsFile
	 */
	public $jsFiles;


	/**
	 * Initializes the view component.
	 */
	public function init()
	{
		parent::init();
		if (is_array($this->theme)) {
			$this->theme = Yii::createObject($this->theme);
		}
	}

	/**
	 * Renders a view.
	 *
	 * This method delegates the call to the [[context]] object:
	 *
	 * - If [[context]] is a controller, the [[Controller::renderPartial()]] method will be called;
	 * - If [[context]] is a widget, the [[Widget::render()]] method will be called;
	 * - Otherwise, an InvalidCallException exception will be thrown.
	 *
	 * @param string $view the view name. Please refer to [[Controller::findViewFile()]]
	 * and [[Widget::findViewFile()]] on how to specify this parameter.
	 * @param array $params the parameters (name-value pairs) that will be extracted and made available in the view file.
	 * @return string the rendering result
	 * @throws InvalidCallException if [[context]] is neither a controller nor a widget.
	 * @throws InvalidParamException if the view cannot be resolved or the view file does not exist.
	 * @see renderFile
	 */
	public function render($view, $params = array())
	{
		if ($this->context instanceof Controller) {
			return $this->context->renderPartial($view, $params);
		} elseif ($this->context instanceof Widget) {
			return $this->context->render($view, $params);
		} else {
			throw new InvalidCallException('View::render() is not supported for the current context.');
		}
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

		$output = '';
		if ($this->beforeRender($viewFile)) {
			$ext = pathinfo($viewFile, PATHINFO_EXTENSION);
			if (isset($this->renderers[$ext])) {
				if (is_array($this->renderers[$ext])) {
					$this->renderers[$ext] = Yii::createObject($this->renderers[$ext]);
				}
				/** @var ViewRenderer $renderer */
				$renderer = $this->renderers[$ext];
				$output = $renderer->render($this, $viewFile, $params);
			} else {
				$output = $this->renderPhpFile($viewFile, $params);
			}
			$this->afterRender($viewFile, $output);
		}

		$this->context = $oldContext;

		return $output;
	}

	/**
	 * This method is invoked right before [[renderFile()]] renders a view file.
	 * The default implementation will trigger the [[EVENT_BEFORE_RENDER]] event.
	 * If you override this method, make sure you call the parent implementation first.
	 * @param string $viewFile the view file to be rendered
	 * @return boolean whether to continue rendering the view file.
	 */
	public function beforeRender($viewFile)
	{
		$event = new ViewEvent($viewFile);
		$this->trigger(self::EVENT_BEFORE_RENDER, $event);
		return $event->isValid;
	}

	/**
	 * This method is invoked right after [[renderFile()]] renders a view file.
	 * The default implementation will trigger the [[EVENT_AFTER_RENDER]] event.
	 * If you override this method, make sure you call the parent implementation first.
	 * @param string $viewFile the view file to be rendered
	 * @param string $output the rendering result of the view file. Updates to this parameter
	 * will be passed back and returned by [[renderFile()]].
	 */
	public function afterRender($viewFile, &$output)
	{
		if ($this->hasEventHandlers(self::EVENT_AFTER_RENDER)) {
			$event = new ViewEvent($viewFile);
			$event->output = $output;
			$this->trigger(self::EVENT_AFTER_RENDER, $event);
			$output = $event->output;
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
	 * Renders dynamic content returned by the given PHP statements.
	 * This method is mainly used together with content caching (fragment caching and page caching)
	 * when some portions of the content (called *dynamic content*) should not be cached.
	 * The dynamic content must be returned by some PHP statements.
	 * @param string $statements the PHP statements for generating the dynamic content.
	 * @return string the placeholder of the dynamic content, or the dynamic content if there is no
	 * active content cache currently.
	 */
	public function renderDynamic($statements)
	{
		if (!empty($this->cacheStack)) {
			$n = count($this->dynamicPlaceholders);
			$placeholder = "<![CDATA[YII-DYNAMIC-$n]]>";
			$this->addDynamicPlaceholder($placeholder, $statements);
			return $placeholder;
		} else {
			return $this->evaluateDynamicContent($statements);
		}
	}

	/**
	 * Adds a placeholder for dynamic content.
	 * This method is internally used.
	 * @param string $placeholder the placeholder name
	 * @param string $statements the PHP statements for generating the dynamic content
	 */
	public function addDynamicPlaceholder($placeholder, $statements)
	{
		foreach ($this->cacheStack as $cache) {
			$cache->dynamicPlaceholders[$placeholder] = $statements;
		}
		$this->dynamicPlaceholders[$placeholder] = $statements;
	}

	/**
	 * Evaluates the given PHP statements.
	 * This method is mainly used internally to implement dynamic content feature.
	 * @param string $statements the PHP statements to be evaluated.
	 * @return mixed the return value of the PHP statements.
	 */
	public function evaluateDynamicContent($statements)
	{
		return eval($statements);
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
		if (!isset($properties['view'])) {
			$properties['view'] = $this;
		}
		return Yii::createObject($properties);
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
		$this->widgetStack[] = $widget;
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
		$widget = array_pop($this->widgetStack);
		if ($widget instanceof Widget) {
			$widget->run();
			return $widget;
		} else {
			throw new InvalidCallException("Unmatched beginWidget() and endWidget() calls.");
		}
	}

	/**
	 * Begins recording a block.
	 * This method is a shortcut to beginning [[yii\widgets\Block]]
	 * @param string $id the block ID.
	 * @param boolean $renderInPlace whether to render the block content in place.
	 * Defaults to false, meaning the captured block will not be displayed.
	 * @return \yii\widgets\Block the Block widget instance
	 */
	public function beginBlock($id, $renderInPlace = false)
	{
		return $this->beginWidget('yii\widgets\Block', array(
			'id' => $id,
			'renderInPlace' => $renderInPlace,
		));
	}

	/**
	 * Ends recording a block.
	 */
	public function endBlock()
	{
		$this->endWidget();
	}

	/**
	 * Begins the rendering of content that is to be decorated by the specified view.
	 * This method can be used to implement nested layout. For example, a layout can be embedded
	 * in another layout file specified as '@app/view/layouts/base' like the following:
	 *
	 * ~~~
	 * <?php $this->beginContent('@app/view/layouts/base'); ?>
	 * ...layout content here...
	 * <?php $this->endContent(); ?>
	 * ~~~
	 *
	 * @param string $viewFile the view file that will be used to decorate the content enclosed by this widget.
	 * This can be specified as either the view file path or path alias.
	 * @param array $params the variables (name => value) to be extracted and made available in the decorative view.
	 * @return \yii\widgets\ContentDecorator the ContentDecorator widget instance
	 * @see \yii\widgets\ContentDecorator
	 */
	public function beginContent($viewFile, $params = array())
	{
		return $this->beginWidget('yii\widgets\ContentDecorator', array(
			'viewFile' => $viewFile,
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
	 * if ($this->beginCache($id)) {
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
		$cache = $this->beginWidget('yii\widgets\FragmentCache', $properties);
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


	private $_assetManager;

	/**
	 * Registers the asset manager being used by this view object.
	 * @return \yii\web\AssetManager the asset manager. Defaults to the "assetManager" application component.
	 */
	public function getAssetManager()
	{
		return $this->_assetManager ?: Yii::$app->getAssetManager();
	}

	/**
	 * Sets the asset manager.
	 * @param \yii\web\AssetManager $value the asset manager
	 */
	public function setAssetManager($value)
	{
		$this->_assetManager = $value;
	}

	/**
	 * Marks the beginning of an HTML page.
	 */
	public function beginPage()
	{
		ob_start();
		ob_implicit_flush(false);

		$this->trigger(self::EVENT_BEGIN_PAGE);
	}

	/**
	 * Marks the ending of an HTML page.
	 */
	public function endPage()
	{
		$this->trigger(self::EVENT_END_PAGE);

		$content = ob_get_clean();
		echo strtr($content, array(
			self::PL_HEAD => $this->renderHeadHtml(),
			self::PL_BODY_BEGIN => $this->renderBodyBeginHtml(),
			self::PL_BODY_END => $this->renderBodyEndHtml(),
		));

		unset(
			$this->assetBundles,
			$this->metaTags,
			$this->linkTags,
			$this->css,
			$this->cssFiles,
			$this->js,
			$this->jsFiles
		);
	}

	/**
	 * Marks the beginning of an HTML body section.
	 */
	public function beginBody()
	{
		echo self::PL_BODY_BEGIN;
	}

	/**
	 * Marks the ending of an HTML body section.
	 */
	public function endBody()
	{
		echo self::PL_BODY_END;
	}

	/**
	 * Marks the position of an HTML head section.
	 */
	public function head()
	{
		echo self::PL_HEAD;
	}

	/**
	 * Registers the named asset bundle.
	 * All dependent asset bundles will be registered.
	 * @param string $name the name of the asset bundle.
	 * @throws InvalidConfigException if the asset bundle does not exist or a circular dependency is detected
	 */
	public function registerAssetBundle($name)
	{
		if (!isset($this->assetBundles[$name])) {
			$am = $this->getAssetManager();
			$bundle = $am->getBundle($name);
			if ($bundle !== null) {
				$this->assetBundles[$name] = false;
				$bundle->registerAssets($this);
				$this->assetBundles[$name] = true;
			} else {
				throw new InvalidConfigException("Unknown asset bundle: $name");
			}
		} elseif ($this->assetBundles[$name] === false) {
			throw new InvalidConfigException("A circular dependency is detected for bundle '$name'.");
		}
	}

	/**
	 * Registers a meta tag.
	 * @param array $options the HTML attributes for the meta tag.
	 * @param string $key the key that identifies the meta tag. If two meta tags are registered
	 * with the same key, the latter will overwrite the former. If this is null, the new meta tag
	 * will be appended to the existing ones.
	 */
	public function registerMetaTag($options, $key = null)
	{
		if ($key === null) {
			$this->metaTags[] = Html::tag('meta', '', $options);
		} else {
			$this->metaTags[$key] = Html::tag('meta', '', $options);
		}
	}

	/**
	 * Registers a link tag.
	 * @param array $options the HTML attributes for the link tag.
	 * @param string $key the key that identifies the link tag. If two link tags are registered
	 * with the same key, the latter will overwrite the former. If this is null, the new link tag
	 * will be appended to the existing ones.
	 */
	public function registerLinkTag($options, $key = null)
	{
		if ($key === null) {
			$this->linkTags[] = Html::tag('link', '', $options);
		} else {
			$this->linkTags[$key] = Html::tag('link', '', $options);
		}
	}

	/**
	 * Registers a CSS code block.
	 * @param string $css the CSS code block to be registered
	 * @param array $options the HTML attributes for the style tag.
	 * @param string $key the key that identifies the CSS code block. If null, it will use
	 * $css as the key. If two CSS code blocks are registered with the same key, the latter
	 * will overwrite the former.
	 */
	public function registerCss($css, $options = array(), $key = null)
	{
		$key = $key ?: $css;
		$this->css[$key] = Html::style($css, $options);
	}

	/**
	 * Registers a CSS file.
	 * @param string $url the CSS file to be registered.
	 * @param array $options the HTML attributes for the link tag.
	 * @param string $key the key that identifies the CSS script file. If null, it will use
	 * $url as the key. If two CSS files are registered with the same key, the latter
	 * will overwrite the former.
	 */
	public function registerCssFile($url, $options = array(), $key = null)
	{
		$key = $key ?: $url;
		$this->cssFiles[$key] = Html::cssFile($url, $options);
	}

	/**
	 * Registers a JS code block.
	 * @param string $js the JS code block to be registered
	 * @param array $options the HTML attributes for the script tag. A special option
	 * named "position" is supported which specifies where the JS script tag should be inserted
	 * in a page. The possible values of "position" are:
	 *
	 * - [[POS_HEAD]]: in the head section
	 * - [[POS_BEGIN]]: at the beginning of the body section
	 * - [[POS_END]]: at the end of the body section
	 *
	 * @param string $key the key that identifies the JS code block. If null, it will use
	 * $js as the key. If two JS code blocks are registered with the same key, the latter
	 * will overwrite the former.
	 */
	public function registerJs($js, $options = array(), $key = null)
	{
		$position = isset($options['position']) ? $options['position'] : self::POS_END;
		unset($options['position']);
		$key = $key ?: $js;
		$this->js[$position][$key] = Html::script($js, $options);
	}

	/**
	 * Registers a JS file.
	 * @param string $url the JS file to be registered.
	 * @param array $options the HTML attributes for the script tag. A special option
	 * named "position" is supported which specifies where the JS script tag should be inserted
	 * in a page. The possible values of "position" are:
	 *
	 * - [[POS_HEAD]]: in the head section
	 * - [[POS_BEGIN]]: at the beginning of the body section
	 * - [[POS_END]]: at the end of the body section
	 *
	 * @param string $key the key that identifies the JS script file. If null, it will use
	 * $url as the key. If two JS files are registered with the same key, the latter
	 * will overwrite the former.
	 */
	public function registerJsFile($url, $options = array(), $key = null)
	{
		$position = isset($options['position']) ? $options['position'] : self::POS_END;
		unset($options['position']);
		$key = $key ?: $url;
		$this->jsFiles[$position][$key] = Html::jsFile($url, $options);
	}

	/**
	 * Renders the content to be inserted in the head section.
	 * The content is rendered using the registered meta tags, link tags, CSS/JS code blocks and files.
	 * @return string the rendered content
	 */
	protected function renderHeadHtml()
	{
		$lines = array();
		if (!empty($this->metaTags)) {
			$lines[] = implode("\n", $this->metaTags);
		}
		if (!empty($this->linkTags)) {
			$lines[] = implode("\n", $this->linkTags);
		}
		if (!empty($this->cssFiles)) {
			$lines[] = implode("\n", $this->cssFiles);
		}
		if (!empty($this->css)) {
			$lines[] = implode("\n", $this->css);
		}
		if (!empty($this->jsFiles[self::POS_HEAD])) {
			$lines[] = implode("\n", $this->jsFiles[self::POS_HEAD]);
		}
		if (!empty($this->js[self::POS_HEAD])) {
			$lines[] = implode("\n", $this->js[self::POS_HEAD]);
		}
		return implode("\n", $lines);
	}

	/**
	 * Renders the content to be inserted at the beginning of the body section.
	 * The content is rendered using the registered JS code blocks and files.
	 * @return string the rendered content
	 */
	protected function renderBodyBeginHtml()
	{
		$lines = array();
		if (!empty($this->jsFiles[self::POS_BEGIN])) {
			$lines[] = implode("\n", $this->jsFiles[self::POS_BEGIN]);
		}
		if (!empty($this->js[self::POS_BEGIN])) {
			$lines[] = implode("\n", $this->js[self::POS_BEGIN]);
		}
		return implode("\n", $lines);
	}

	/**
	 * Renders the content to be inserted at the end of the body section.
	 * The content is rendered using the registered JS code blocks and files.
	 * @return string the rendered content
	 */
	protected function renderBodyEndHtml()
	{
		$lines = array();
		if (!empty($this->jsFiles[self::POS_END])) {
			$lines[] = implode("\n", $this->jsFiles[self::POS_END]);
		}
		if (!empty($this->js[self::POS_END])) {
			$lines[] = implode("\n", $this->js[self::POS_END]);
		}
		return implode("\n", $lines);
	}
}
