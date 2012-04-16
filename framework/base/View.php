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
	 * @throws Exception
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

	public function renderFile($file, $params = array())
	{
		$this->renderFileInternal($file, $params);
	}

	public function widget($class, $properties = array(), $returnOutput = false)
	{
		
	}

	public function beginWidget($class, $properties = array())
	{

	}

	public function endWidget($returnOutput = false)
	{

	}

	public function beginClip($id, $properties = array())
	{
			
	}

	public function endClip()
	{
		
	}
	
	public function beginCache($id, $properties = array())
	{
			
	}
	
	public function endCache()
	{
		
	}
	
	public function beginContent()
	{
		
	}

	public function endContent()
	{

	}

	protected function renderFileInternal($_file_, $_params_ = array())
	{
		extract($_params_, EXTR_OVERWRITE);
		ob_start();
		ob_implicit_flush(false);
		require($_file_);
		return ob_get_clean();
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
			$file = \Yii::getAlias($view[0]);
		} elseif (!empty($this->basePath)) {
			$basePaths = is_array($this->basePath) ? $this->basePath : array($this->basePath);
			$found = false;
			foreach ($basePaths as $basePath) {
				$file = $basePath . DIRECTORY_SEPARATOR . $view;
				if (is_file($file)) {
					$found = true;
					break;
				}
			}
			if (!$found) {
				return false;
			}
		}
		$file = FileHelper::localize($file, $this->language, $this->sourceLanguage);
		return is_file($file) ? $file : false;
	}
}