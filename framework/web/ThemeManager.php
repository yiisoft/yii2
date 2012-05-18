<?php
/**
 * CThemeManager class file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CThemeManager manages the themes for the Web application.
 *
 * A theme is a collection of view/layout files and resource files
 * (e.g. css, image, js files). When a theme is active, {@link CController}
 * will look for the specified view/layout under the theme folder first.
 * The corresponding view/layout files will be used if the theme provides them.
 * Otherwise, the default view/layout files will be used.
 *
 * By default, each theme is organized as a directory whose name is the theme name.
 * All themes are located under the "WebRootPath/themes" directory.
 *
 * To activate a theme, set the {@link CWebApplication::setTheme theme} property
 * to be the name of that theme.
 *
 * Since a self-contained theme often contains resource files that are made
 * Web accessible, please make sure the view/layout files are protected from Web access.
 *
 * @property array $themeNames List of available theme names.
 * @property string $basePath The base path for all themes. Defaults to "WebRootPath/themes".
 * @property string $baseUrl The base URL for all themes. Defaults to "/WebRoot/themes".
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id$
 * @package system.web
 * @since 1.0
 */
class CThemeManager extends CApplicationComponent
{
	/**
	 * default themes base path
	 */
	const DEFAULT_BASEPATH='themes';

	/**
	 * @var string the name of the theme class for representing a theme.
	 * Defaults to {@link CTheme}. This can also be a class name in dot syntax.
	 */
	public $themeClass='CTheme';

	private $_basePath=null;
	private $_baseUrl=null;


	/**
	 * @param string $name name of the theme to be retrieved
	 * @return CTheme the theme retrieved. Null if the theme does not exist.
	 */
	public function getTheme($name)
	{
		$themePath=$this->getBasePath().DIRECTORY_SEPARATOR.$name;
		if(is_dir($themePath))
		{
			$class=Yii::import($this->themeClass, true);
			return new $class($name,$themePath,$this->getBaseUrl().'/'.$name);
		}
		else
			return null;
	}

	/**
	 * @return array list of available theme names
	 */
	public function getThemeNames()
	{
		static $themes;
		if($themes===null)
		{
			$themes=array();
			$basePath=$this->getBasePath();
			$folder=@opendir($basePath);
			while(($file=@readdir($folder))!==false)
			{
				if($file!=='.' && $file!=='..' && $file!=='.svn' && $file!=='.gitignore' && is_dir($basePath.DIRECTORY_SEPARATOR.$file))
					$themes[]=$file;
			}
			closedir($folder);
			sort($themes);
		}
		return $themes;
	}

	/**
	 * @return string the base path for all themes. Defaults to "WebRootPath/themes".
	 */
	public function getBasePath()
	{
		if($this->_basePath===null)
			$this->setBasePath(dirname(Yii::app()->getRequest()->getScriptFile()).DIRECTORY_SEPARATOR.self::DEFAULT_BASEPATH);
		return $this->_basePath;
	}

	/**
	 * @param string $value the base path for all themes.
	 * @throws CException if the base path does not exist
	 */
	public function setBasePath($value)
	{
		$this->_basePath=realpath($value);
		if($this->_basePath===false || !is_dir($this->_basePath))
			throw new CException(Yii::t('yii','Theme directory "{directory}" does not exist.',array('{directory}'=>$value)));
	}

	/**
	 * @return string the base URL for all themes. Defaults to "/WebRoot/themes".
	 */
	public function getBaseUrl()
	{
		if($this->_baseUrl===null)
			$this->_baseUrl=Yii::app()->getBaseUrl().'/'.self::DEFAULT_BASEPATH;
		return $this->_baseUrl;
	}

	/**
	 * @param string $value the base URL for all themes.
	 */
	public function setBaseUrl($value)
	{
		$this->_baseUrl=rtrim($value,'/');
	}
}
