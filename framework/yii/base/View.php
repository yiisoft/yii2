<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;
use yii\helpers\FileHelper;
use yii\widgets\Block;
use yii\widgets\ContentDecorator;
use yii\widgets\FragmentCache;

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
	 * @event Event an event that is triggered by [[beginPage()]].
	 */
	const EVENT_BEGIN_PAGE = 'beginPage';
	/**
	 * @event Event an event that is triggered by [[endPage()]].
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
	 * @var ViewContextInterface the context under which the [[renderFile()]] method is being invoked.
	 */
	public $context;
	/**
	 * @var mixed custom parameters that are shared among view templates.
	 */
	public $params = [];
	/**
	 * @var array a list of available renderers indexed by their corresponding supported file extensions.
	 * Each renderer may be a view renderer object or the configuration for creating the renderer object.
	 * For example, the following configuration enables both Smarty and Twig view renderers:
	 *
	 * ~~~
	 * [
	 *     'tpl' => ['class' => 'yii\smarty\ViewRenderer'],
	 *     'twig' => ['class' => 'yii\twig\ViewRenderer'],
	 * ]
	 * ~~~
	 *
	 * If no renderer is available for the given view file, the view file will be treated as a normal PHP
	 * and rendered via [[renderPhpFile()]].
	 */
	public $renderers;
	/**
	 * @var string the default view file extension. This will be appended to view file names if they don't have file extensions.
	 */
	public $defaultExtension = 'php';
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
	 * @var array a list of currently active fragment cache widgets. This property
	 * is used internally to implement the content caching feature. Do not modify it directly.
	 * @internal
	 */
	public $cacheStack = [];
	/**
	 * @var array a list of placeholders for embedding dynamic contents. This property
	 * is used internally to implement the content caching feature. Do not modify it directly.
	 * @internal
	 */
	public $dynamicPlaceholders = [];


	/**
	 * Initializes the view component.
	 */
	public function init()
	{
		parent::init();
		if (is_array($this->theme)) {
			if (!isset($this->theme['class'])) {
				$this->theme['class'] = 'yii\base\Theme';
			}
			$this->theme = Yii::createObject($this->theme);
		}
	}

	/**
	 * Renders a view.
	 *
	 * The view to be rendered can be specified in one of the following formats:
	 *
	 * - path alias (e.g. "@app/views/site/index");
	 * - absolute path within application (e.g. "//site/index"): the view name starts with double slashes.
	 *   The actual view file will be looked for under the [[Application::viewPath|view path]] of the application.
	 * - absolute path within current module (e.g. "/site/index"): the view name starts with a single slash.
	 *   The actual view file will be looked for under the [[Module::viewPath|view path]] of [[module]].
	 * - resolving any other format will be performed via [[ViewContext::findViewFile()]].
	 *
	 * @param string $view the view name. Please refer to [[Controller::findViewFile()]]
	 * and [[Widget::findViewFile()]] on how to specify this parameter.
	 * @param array $params the parameters (name-value pairs) that will be extracted and made available in the view file.
	 * @param object $context the context that the view should use for rendering the view. If null,
	 * existing [[context]] will be used.
	 * @return string the rendering result
	 * @throws InvalidParamException if the view cannot be resolved or the view file does not exist.
	 * @see renderFile()
	 */
	public function render($view, $params = [], $context = null)
	{
		$viewFile = $this->findViewFile($view, $context);
		return $this->renderFile($viewFile, $params, $context);
	}

	/**
	 * Finds the view file based on the given view name.
	 * @param string $view the view name or the path alias of the view file. Please refer to [[render()]]
	 * on how to specify this parameter.
	 * @param object $context the context that the view should be used to search the view file. If null,
	 * existing [[context]] will be used.
	 * @return string the view file path. Note that the file may not exist.
	 * @throws InvalidCallException if [[context]] is required and invalid.
	 */
	protected function findViewFile($view, $context = null)
	{
		if (strncmp($view, '@', 1) === 0) {
			// e.g. "@app/views/main"
			$file = Yii::getAlias($view);
		} elseif (strncmp($view, '//', 2) === 0) {
			// e.g. "//layouts/main"
			$file = Yii::$app->getViewPath() . DIRECTORY_SEPARATOR . ltrim($view, '/');
		} elseif (strncmp($view, '/', 1) === 0) {
			// e.g. "/site/index"
			if (Yii::$app->controller !== null) {
				$file = Yii::$app->controller->module->getViewPath() . DIRECTORY_SEPARATOR . ltrim($view, '/');
			} else {
				throw new InvalidCallException("Unable to locate view file for view '$view': no active controller.");
			}
		} else {
			// context required
			if ($context === null) {
				$context = $this->context;
			}
			if ($context instanceof ViewContextInterface) {
				$file = $context->findViewFile($view);
			} else {
				throw new InvalidCallException("Unable to locate view file for view '$view': no active view context.");
			}
		}

		if (pathinfo($file, PATHINFO_EXTENSION) !== '') {
			return $file;
		}
		$path = $file . '.' . $this->defaultExtension;
		if ($this->defaultExtension !== 'php' && !is_file($path)) {
			$path = $file . '.php';
		}
		return $path;
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
	public function renderFile($viewFile, $params = [], $context = null)
	{
		$viewFile = Yii::getAlias($viewFile);
		if ($this->theme !== null) {
			$viewFile = $this->theme->applyTo($viewFile);
		}
		if (is_file($viewFile)) {
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
			Yii::trace("Rendering view file: $viewFile", __METHOD__);
			$ext = pathinfo($viewFile, PATHINFO_EXTENSION);
			if (isset($this->renderers[$ext])) {
				if (is_array($this->renderers[$ext]) || is_string($this->renderers[$ext])) {
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
	public function renderPhpFile($_file_, $_params_ = [])
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
	 * Begins recording a block.
	 * This method is a shortcut to beginning [[Block]]
	 * @param string $id the block ID.
	 * @param boolean $renderInPlace whether to render the block content in place.
	 * Defaults to false, meaning the captured block will not be displayed.
	 * @return Block the Block widget instance
	 */
	public function beginBlock($id, $renderInPlace = false)
	{
		return Block::begin([
			'id' => $id,
			'renderInPlace' => $renderInPlace,
			'view' => $this,
		]);
	}

	/**
	 * Ends recording a block.
	 */
	public function endBlock()
	{
		Block::end();
	}

	/**
	 * Begins the rendering of content that is to be decorated by the specified view.
	 * This method can be used to implement nested layout. For example, a layout can be embedded
	 * in another layout file specified as '@app/views/layouts/base.php' like the following:
	 *
	 * ~~~
	 * <?php $this->beginContent('@app/views/layouts/base.php'); ?>
	 * ...layout content here...
	 * <?php $this->endContent(); ?>
	 * ~~~
	 *
	 * @param string $viewFile the view file that will be used to decorate the content enclosed by this widget.
	 * This can be specified as either the view file path or path alias.
	 * @param array $params the variables (name => value) to be extracted and made available in the decorative view.
	 * @return ContentDecorator the ContentDecorator widget instance
	 * @see ContentDecorator
	 */
	public function beginContent($viewFile, $params = [])
	{
		return ContentDecorator::begin([
			'viewFile' => $viewFile,
			'params' => $params,
			'view' => $this,
		]);
	}

	/**
	 * Ends the rendering of content.
	 */
	public function endContent()
	{
		ContentDecorator::end();
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
	 * @param array $properties initial property values for [[FragmentCache]]
	 * @return boolean whether you should generate the content for caching.
	 * False if the cached version is available.
	 */
	public function beginCache($id, $properties = [])
	{
		$properties['id'] = $id;
		$properties['view'] = $this;
		/** @var FragmentCache $cache */
		$cache = FragmentCache::begin($properties);
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
		FragmentCache::end();
	}

	/**
	 * Marks the beginning of a page.
	 */
	public function beginPage()
	{
		ob_start();
		ob_implicit_flush(false);

		$this->trigger(self::EVENT_BEGIN_PAGE);
	}

	/**
	 * Marks the ending of a page.
	 */
	public function endPage()
	{
		$this->trigger(self::EVENT_END_PAGE);
		ob_end_flush();
	}
}
