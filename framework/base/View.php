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
use yii\util\ArrayHelper;

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
	 * Renders a view.
	 *
	 * The method first identifies the actual view file corresponding to the specified view.
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
	 * @param string $view the view to be rendered. This can be a path alias or a path relative to [[basePath]].
	 * @param array $params the parameters that should be made available in the view. The PHP function `extract()`
	 * will be called on this variable to extract the variables from this parameter.
	 * @return string the rendering result
	 * @throws Exception if the view  file cannot be found
	 */
	public function render($view, $params = array())
	{
		$file = $this->findViewFile($view);
		if ($file !== false) {
			$this->renderFile($file, $params);
		} else {
			throw new Exception("Unable to find the view file for view '$view'.");
		}
	}

	public function renderFile($file, $params = array())
	{
		$this->renderFileInternal($file, $params);
	}

	public function widget($class, $properties = array())
	{
		$widget = $this->createWidget($class, $properties);
		$widget->run();
		return $widget;
	}

	private $_widgetStack = array();

	public function beginWidget($class, $properties = array())
	{
		$widget = $this->createWidget($class, $properties);
		$this->_widgetStack[] = $widget;
		return $widget;
	}

	public function endWidget()
	{
		if (($widget = array_pop($this->_widgetStack)) !== null) {
			$widget->run();
			return $widget;
		} else {
			throw new Exception("Unmatched beginWidget() and endWidget() calls.");
		}
	}

	public function createWidget($class, $properties = array())
	{
		$properties['class'] = $class;

		// todo: widget skin should be something global, similar to theme
		if ($this->enableSkin) {
			if ($this->skinnableWidgets === null || in_array($class, $this->skinnableWidgets)) {
				$skinName = isset($properties['skin']) ? $properties['skin'] : 'default';
				if ($skinName !== false && ($skin = $this->getSkin($class, $skinName)) !== array()) {
					$properties = $properties === array() ? $skin : ArrayHelper::merge($skin, $properties);
				}
			}
		}

		return \Yii::createObject($properties, $this->owner);
	}

	/**
	 * Begins recording a clip.
	 * This method is a shortcut to beginning [[yii\web\widgets\ClipWidget]]
	 * @param string $id the clip ID.
	 * @param array $properties initial property values for [[yii\web\widgets\ClipWidget]]
	 */
	public function beginClip($id, $properties = array())
	{
		$properties['id'] = $id;
		$this->beginWidget('yii\web\widgets\ClipWidget', $properties);
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
	 * @param array $properties initial property values for [[yii\web\widgets\OutputCache]]
	 * @return boolean whether we need to generate content for caching. False if cached version is available.
	 * @see endCache
	 */
	public function beginCache($id, $properties = array())
	{
		$properties['id'] = $id;
		$cache = $this->beginWidget('yii\web\widgets\OutputCache', $properties);
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
	 * @see yii\web\widgets\ContentDecorator
	 */
	public function beginContent($view = null, $params = array())
	{
		$this->beginWidget('yii\web\widgets\ContentDecorator', array(
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

	protected function renderFileInternal($_file_, $_params_ = array())
	{
		extract($_params_, EXTR_OVERWRITE);
		require($_file_);
	}

	public function findViewFile($view)
	{
		if ($view[0] === '/') {
			throw new Exception('The view name "$view" should not start with a slash "/".');
		}

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