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
	private $_name;
	private $_basePath;
	private $_baseUrl;

	/**
	 * Constructor.
	 * @param string $name name of the theme
	 * @param string $basePath base theme path
	 * @param string $baseUrl base theme URL
	 */
	public function __construct($name, $basePath, $baseUrl)
	{
		$this->_name = $name;
		$this->_baseUrl = $baseUrl;
		$this->_basePath = $basePath;
	}

	/**
	 * @return string theme name
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * @return string the relative URL to the theme folder (without ending slash)
	 */
	public function getBaseUrl()
	{
		return $this->_baseUrl;
	}

	/**
	 * @return string the file path to the theme folder
	 */
	public function getBasePath()
	{
		return $this->_basePath;
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
