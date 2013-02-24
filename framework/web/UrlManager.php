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
use yii\base\Component;

/**
 * UrlManager handles HTTP request parsing and creation of URLs based on a set of rules.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class UrlManager extends Component
{
	/**
	 * @var boolean whether to enable pretty URLs. Instead of putting all parameters in the query
	 * string part of a URL, pretty URLs allow using path info to represent some of the parameters
	 * and can thus produce more user-friendly URLs, such as "/news/Yii-is-released", instead of
	 * "/index.php?r=news/view&id=100".
	 */
	public $enablePrettyUrl = false;
	/**
	 * @var array the rules for creating and parsing URLs when [[enablePrettyUrl]] is true.
	 * This property is used only if [[enablePrettyUrl]] is true. Each element in the array
	 * is the configuration of creating a single URL rule whose class by default is [[defaultRuleClass]].
	 * If you modify this property after the UrlManager object is created, make sure
	 * you populate the array with rule objects instead of rule configurations.
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
	 * @var string the GET variable name for route. This property is used only if [[enablePrettyUrl]] is false.
	 */
	public $routeVar = 'r';
	/**
	 * @var string the ID of the cache component that is used to cache the parsed URL rules.
	 * Defaults to 'cache' which refers to the primary cache component registered with the application.
	 * Set this property to false if you do not want to cache the URL rules.
	 */
	public $cacheID = 'cache';
	/**
	 * @var string the default class name for creating URL rule instances
	 * when it is not specified in [[rules]].
	 */
	public $defaultRuleClass = 'yii\web\UrlRule';

	private $_baseUrl;
	private $_hostInfo;


	/**
	 * Initializes the application component.
	 */
	public function init()
	{
		parent::init();
		$this->compileRules();
	}

	/**
	 * Parses the URL rules.
	 */
	protected function compileRules()
	{
		if (!$this->enablePrettyUrl || $this->rules === array()) {
			return;
		}
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
				} else {
					// suffix doesn't match
					return false;
				}
			}

			return array($pathInfo, array());
		} else {
			$route = $request->getParam($this->routeVar);
			if (is_array($route)) {
				$route = '';
			}
			return array((string)$route, array());
		}
	}

	/**
	 * Creates a URL using the given route and parameters.
	 * The URL created is a relative one. Use [[createAbsoluteUrl()]] to create an absolute URL.
	 * @param string $route the route
	 * @param array $params the parameters (name-value pairs)
	 * @return string the created URL
	 */
	public function createUrl($route, $params = array())
	{
		$anchor = isset($params['#']) ? '#' . $params['#'] : '';
		unset($params['#']);

		$route = trim($route, '/');
		$baseUrl = $this->getBaseUrl();

		if ($this->enablePrettyUrl) {
			/** @var $rule UrlRule */
			foreach ($this->rules as $rule) {
				if (($url = $rule->createUrl($this, $route, $params)) !== false) {
					return rtrim($baseUrl, '/') . '/' . $url . $anchor;
				}
			}

			if ($this->suffix !== null) {
				$route .= $this->suffix;
			}
			if ($params !== array()) {
				$route .= '?' . http_build_query($params);
			}
			return rtrim($baseUrl, '/') . '/' . $route . $anchor;
		} else {
			$url = $baseUrl . '?' . $this->routeVar . '=' . $route;
			if ($params !== array()) {
				$url .= '&' . http_build_query($params);
			}
			return $url;
		}
	}

	/**
	 * Creates an absolute URL using the given route and parameters.
	 * This method prepends the URL created by [[createUrl()]] with the [[hostInfo]].
	 * @param string $route the route
	 * @param array $params the parameters (name-value pairs)
	 * @return string the created URL
	 * @see createUrl()
	 */
	public function createAbsoluteUrl($route, $params = array())
	{
		return $this->getHostInfo() . $this->createUrl($route, $params);
	}

	/**
	 * Returns the base URL that is used by [[createUrl()]] to prepend URLs it creates.
	 * It defaults to [[Request::scriptUrl]] if [[showScriptName]] is true or [[enablePrettyUrl]] is false;
	 * otherwise, it defaults to [[Request::baseUrl]].
	 * @return string the base URL that is used by [[createUrl()]] to prepend URLs it creates.
	 */
	public function getBaseUrl()
	{
		if ($this->_baseUrl === null) {
			/** @var $request \yii\web\Request */
			$request = Yii::$app->getRequest();
			$this->_baseUrl = $this->showScriptName || !$this->enablePrettyUrl ? $request->getScriptUrl() : $request->getBaseUrl();
		}
		return $this->_baseUrl;
	}

	/**
	 * Sets the base URL that is used by [[createUrl()]] to prepend URLs it creates.
	 * @param string $value the base URL that is used by [[createUrl()]] to prepend URLs it creates.
	 */
	public function setBaseUrl($value)
	{
		$this->_baseUrl = $value;
	}

	/**
	 * Returns the host info that is used by [[createAbsoluteUrl()]] to prepend URLs it creates.
	 * @return string the host info (e.g. "http://www.example.com") that is used by [[createAbsoluteUrl()]] to prepend URLs it creates.
	 */
	public function getHostInfo()
	{
		if ($this->_hostInfo === null) {
			$this->_hostInfo = Yii::$app->getRequest()->getHostInfo();
		}
		return $this->_hostInfo;
	}

	/**
	 * Sets the host info that is used by [[createAbsoluteUrl()]] to prepend URLs it creates.
	 * @param string $value the host info (e.g. "http://www.example.com") that is used by [[createAbsoluteUrl()]] to prepend URLs it creates.
	 */
	public function setHostInfo($value)
	{
		$this->_hostInfo = rtrim($value, '/');
	}
}
