<?php
/**
 * UrlManager class file
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use \yii\base\Component;

/**
 * UrlManager manages the URLs of Yii applications.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class UrlManager extends Component
{
	/**
	 * @var boolean whether to enable pretty URLs.
	 */
	public $enablePrettyUrl = false;
	/**
	 * @var array the rules for creating and parsing URLs when [[enablePrettyUrl]] is true.
	 * This property is used only if [[enablePrettyUrl]] is true. Each element in the array
	 * is the configuration of creating a single URL rule whose class by default is [[defaultRuleClass]].
	 * If you modify this property after the UrlManager object is created, make sure
	 * populate the array with the rule objects instead of configurations.
	 */
	public $rules = array();
	/**
	 * @var string the URL suffix used when in 'path' format.
	 * For example, ".html" can be used so that the URL looks like pointing to a static HTML page.
	 * This property is used only if [[enablePrettyUrl]] is true.
	 */
	public $suffix;
	/**
	 * @var boolean whether to show entry script name in the constructed URL. Defaults to true.
	 * This property is used only if [[enablePrettyUrl]] is true.
	 */
	public $showScriptName = true;
	/**
	 * @var string the GET variable name for route. Defaults to 'r'.
	 */
	public $routeVar = 'r';
	/**
	 * @var string the ID of the cache component that is used to cache the parsed URL rules.
	 * Defaults to 'cache' which refers to the primary cache component registered with the application.
	 * Set this property to false if you want to disable caching URL rules.
	 */
	public $cacheID = 'cache';
	/**
	 * @var string the class name or configuration for URL rule instances.
	 * This will be passed to [[\Yii::createObject()]] to create the URL rule instances.
	 */
	public $defaultRuleClass = 'yii\web\UrlRule';

	/**
	 * Initializes the application component.
	 */
	public function init()
	{
		parent::init();

		if ($this->enablePrettyUrl && $this->rules !== array()) {
			$this->compileRules();
		}
	}

	protected function compileRules()
	{
		/**
		 * @var $cache \yii\caching\Cache
		 */
		if ($this->cacheID !== false && ($cache = Yii::$app->getComponent($this->cacheID)) !== null) {
			$key = $cache->buildKey(__CLASS__);
			$hash = md5(json_encode($this->rules));
			if (($data = $cache->get($key)) !== false && isset($data[1]) && $data[1] === $hash) {
				$this->rules = $data[0];
				return;
			}
		}

		foreach ($this->rules as $i => $rule) {
			if (!isset($rule['class'])) {
				$rule['class'] = $this->defaultRuleClass;
			}
			$this->rules[$i] = Yii::createObject($rule);
		}

		if (isset($cache)) {
			$cache->set($key, array($this->rules, $hash));
		}
	}

	/**
	 * Parses the user request.
	 * @param Request $request the request component
	 * @return array|boolean the route and the associated parameters. The latter is always empty
	 * if [[enablePrettyUrl]] is false. False is returned if the current request cannot be successfully parsed.
	 */
	public function parseRequest($request)
	{
		if ($this->enablePrettyUrl) {
			$pathInfo = $request->pathInfo;
			/** @var $rule UrlRule */
			foreach ($this->rules as $rule) {
				if (($result = $rule->parseUrl($this, $pathInfo)) !== false) {
					return $result;
				}
			}

			$suffix = (string)$this->suffix;
			if ($suffix !== '' && $suffix !== '/' && $pathInfo !== '') {
				$n = strlen($this->suffix);
				if (substr($pathInfo, -$n) === $this->suffix) {
					$pathInfo = substr($pathInfo, 0, -$n);
					if ($pathInfo === '') {
						// suffix alone is not allowed
						return false;
					}
				}
			}

			return array($pathInfo, array());
		} else {
			$route = (string)$request->getParam($this->routeVar);
			return array($route, array());
		}
	}

	public function createUrl($route, $params = array())
	{
		$anchor = isset($params['#']) ? '#' . $params['#'] : '';
		unset($anchor['#']);

		$route = trim($route, '/');

		$baseUrl = $this->getBaseUrl();

		if ($this->enablePrettyUrl) {
			/** @var $rule UrlRule */
			foreach ($this->rules as $rule) {
				if (($url = $rule->createUrl($this, $route, $params)) !== false) {
					return $baseUrl . $url . $anchor;
				}
			}

			if ($this->suffix !== null) {
				$route .= $this->suffix;
			}
			if ($params !== array()) {
				$route .= '?' . http_build_query($params);
			}
			return $baseUrl . '/' . $route . $anchor;
		} else {
			$params[$this->routeVar] = $route;
			if (!$this->showScriptName) {
				$baseUrl .= '/';
			}
			return $baseUrl . '?' . http_build_query($params) . $anchor;
		}
	}

	public function createAbsoluteUrl($route, $params = array(), $hostInfo = null)
	{
		if ($hostInfo === null) {
			$hostInfo = $this->getHostInfo();
		}
		return $hostInfo . $this->createUrl($route, $params);
	}

	private $_baseUrl;

	/**
	 * Returns the base URL of the application.
	 * @return string the base URL of the application (the part after host name and before query string).
	 * If {@link showScriptName} is true, it will include the script name part.
	 * Otherwise, it will not, and the ending slashes are stripped off.
	 */
	public function getBaseUrl()
	{
		if ($this->_baseUrl === null) {
			/** @var $request \yii\web\Request */
			$request = Yii::$app->getRequest();
			$this->_baseUrl = $this->showScriptName ? $request->getScriptUrl() : $request->getBaseUrl();
		}
		return $this->_baseUrl;
	}

	public function setBaseUrl($value)
	{
		$this->_baseUrl = trim($value, '/');
	}

	private $_hostInfo;

	public function getHostInfo()
	{
		if ($this->_hostInfo === null) {
			/** @var $request \yii\web\Request */
			$request = Yii::$app->getRequest();
			$this->_hostInfo = $request->getHostInfo();
		}
		return $this->_baseUrl;
	}

	public function setHostInfo($value)
	{
		$this->_hostInfo = $value;
	}

	/**
	 * Removes the URL suffix from path info.
	 * @param string $pathInfo path info part in the URL
	 * @param string $suffix the URL suffix to be removed
	 * @return string path info with URL suffix removed.
	 */
	public function removeSuffix($pathInfo, $suffix)
	{
		$n = strlen($suffix);
		if ($n > 0 && substr($pathInfo, -$n) === $suffix) {
			return substr($pathInfo, 0, -$n);
		} else {
			return $pathInfo;
		}
	}
}
