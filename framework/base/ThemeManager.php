<?php
/**
 * ThemeManager class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ThemeManager manages the themes for the Web application.
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
 * @since 2.0
 */
class ThemeManager extends ApplicationComponent
{
	/**
	 * default themes base path
	 */
	const DEFAULT_BASEPATH = 'themes';

	/**
	 * @var string the name of the theme class for representing a theme.
	 * Defaults to {@link Theme}. This can also be a class name in dot syntax.
	 */
	public $themeClass = 'Theme';
	/**
	 * @var string the base path containing all themes. Defaults to '@entry/themes'.
	 */
	public $basePath = '@entry/themes';
	/**
	 * @var string the base URL for all themes. Defaults to "@www/themes".
	 */
	public $baseUrl = '@www/themes';


	/**
	 * @param string $name name of the theme to be retrieved
	 * @return Theme the theme retrieved. Null if the theme does not exist.
	 */
	public function getTheme($name)
	{
		$themePath = $this->getBasePath() . DIRECTORY_SEPARATOR . $name;
		if (is_dir($themePath)) {
			$class = Yii::import($this->themeClass, true);
			return new $class($name, $themePath, $this->getBaseUrl() . '/' . $name);
		} else {
			return null;
		}
	}

	/**
	 * @return array list of available theme names
	 */
	public function getThemeNames()
	{
		static $themes;
		if ($themes === null) {
			$themes = array();
			$basePath = $this->getBasePath();
			$folder = @opendir($basePath);
			while (($file = @readdir($folder)) !== false) {
				if ($file !== '.' && $file !== '..' && $file !== '.svn' && $file !== '.gitignore' && is_dir($basePath . DIRECTORY_SEPARATOR . $file)) {
					$themes[] = $file;
				}
			}
			closedir($folder);
			sort($themes);
		}
		return $themes;
	}
}
