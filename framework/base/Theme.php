<?php
/**
 * Theme class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * Theme represents an application theme.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Theme extends ApplicationComponent
{
	public $basePath;
	public $baseUrl;

	public function init()
	{
		if ($this->basePath !== null) {
			$this->basePath = \Yii::getAlias($this->basePath, true);
		} else {
			throw new BadConfigException("Theme.basePath must be set.");
		}
		if ($this->baseUrl !== null) {
			$this->baseUrl = \Yii::getAlias($this->baseUrl, true);
		} else {
			throw new BadConfigException("Theme.baseUrl must be set.");
		}
	}

	/**
	 * @param Application|Module|Controller|Object $context
	 * @return string
	 */
	public function getViewPath($context = null)
	{
		$viewPath = $this->basePath . DIRECTORY_SEPARATOR . 'views';
		if ($context === null || $context instanceof Application) {
			return $viewPath;
		} elseif ($context instanceof Controller || $context instanceof Module) {
			return $viewPath . DIRECTORY_SEPARATOR . $context->getUniqueId();
		} else {
			return $viewPath . DIRECTORY_SEPARATOR . str_replace('\\', '_', get_class($context));
		}
	}

	/**
	 * @param Module $module
	 * @return string
	 */
	public function getLayoutPath($module = null)
	{
		return $this->getViewPath($module) . DIRECTORY_SEPARATOR . 'layouts';
	}
}
