<?php
/**
 * Theme class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
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
			$this->basePath = \Yii::getAlias($this->basePath);
		} else {
			throw new Exception("Theme.basePath must be set.");
		}
		if ($this->baseUrl !== null) {
			$this->baseUrl = \Yii::getAlias($this->baseUrl);
		} else {
			throw new Exception("Theme.baseUrl must be set.");
		}
	}

	/**
	 * @param Controller $controller
	 * @return string
	 */
	public function getViewPath($controller = null)
	{
		$path = $this->basePath . DIRECTORY_SEPARATOR . 'views';
		return $controller === null ? $path : $path . DIRECTORY_SEPARATOR . $controller->id;
	}

	public function getLayoutPath($module = null)
	{
		$path = $this->getViewPath($module);
		return $controller === null ? $path : $path . DIRECTORY_SEPARATOR . $controller->id;
	}

	public function getWidgetViewPath($widget)
	{

	}

	/**
	 * @return string the path for controller views. Defaults to 'ThemeRoot/views'.
	 */
	public function getViewPath()
	{
		return $this->_basePath . DIRECTORY_SEPARATOR . 'views';
	}

	/**
	 * Finds the view file for the specified controller's view.
	 * @param CController $controller the controller
	 * @param string $viewName the view name
	 * @return string the view file path. False if the file does not exist.
	 */
	public function getViewFile($controller, $viewName)
	{
		$moduleViewPath = $this->getViewPath();
		if (($module = $controller->getModule()) !== null)
				{
					$moduleViewPath .= '/' . $module->getId();
				}
		return $controller->resolveViewFile($viewName, $this->getViewPath() . '/' . $controller->getUniqueId(), $this->getViewPath(), $moduleViewPath);
	}

	/**
	 * Finds the layout file for the specified controller's layout.
	 * @param CController $controller the controller
	 * @param string $layoutName the layout name
	 * @return string the layout file path. False if the file does not exist.
	 */
	public function getLayoutFile($controller, $layoutName)
	{
		$moduleViewPath = $basePath = $this->getViewPath();
		$module = $controller->getModule();
		if (empty($layoutName)) {
			while ($module !== null) {
				if ($module->layout === false)
					return false;
				if (!empty($module->layout))
					break;
				$module = $module->getParentModule();
			}
			if ($module === null)
				$layoutName = Yii::app()->layout;
			else {
				$layoutName = $module->layout;
				$moduleViewPath .= '/' . $module->getId();
			}
		}
		else if ($module !== null)
			$moduleViewPath .= '/' . $module->getId();

		return $controller->resolveViewFile($layoutName, $moduleViewPath . '/layouts', $basePath, $moduleViewPath);
	}
}
