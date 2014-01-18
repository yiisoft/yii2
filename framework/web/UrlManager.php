<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\Component;
use yii\caching\Cache;

/**
 * UrlManager handles HTTP request parsing and creation of URLs based on a set of rules.
 *
 * UrlManager is configured as an application component in [[yii\base\Application]] by default.
 * You can access that instance via `Yii::$app->urlManager`.
 *
 * You can modify its configuration by adding an array to your application config under `components`
 * as it is shown in the following example:
 *
 * ~~~
 * 'urlManager' => [
 *     'enablePrettyUrl' => true,
 *     'rules' => [
 *         // your rules go here
 *     ],
 *     // ...
 * ]
 * ~~~
 *
 * @property string $baseUrl The base URL that is used by [[createUrl()]] to prepend URLs it creates.
 * @property string $hostInfo The host info (e.g. "http://www.example.com") that is used by
 * [[createAbsoluteUrl()]] to prepend URLs it creates.
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
	 * @var boolean whether to enable strict parsing. If strict parsing is enabled, the incoming
	 * requested URL must match at least one of the [[rules]] in order to be treated as a valid request.
	 * Otherwise, the path info part of the request will be treated as the requested route.
	 * This property is used only when [[enablePrettyUrl]] is true.
	 */
	public $enableStrictParsing = false;
	/**
	 * @var array the rules for creating and parsing URLs when [[enablePrettyUrl]] is true.
	 * This property is used only if [[enablePrettyUrl]] is true. Each element in the array
	 * is the configuration array for creating a single URL rule. The configuration will
	 * be merged with [[ruleConfig]] first before it is used for creating the rule object.
	 *
	 * A special shortcut format can be used if a rule only specifies [[UrlRule::pattern|pattern]]
	 * and [[UrlRule::route|route]]: `'pattern' => 'route'`. That is, instead of using a configuration
	 * array, one can use the key to represent the pattern and the value the corresponding route.
	 * For example, `'post/<id:\d+>' => 'post/view'`.
	 *
	 * For RESTful routing the mentioned shortcut format also allows you to specify the
	 * [[UrlRule::verb|HTTP verb]] that the rule should apply for.
	 * You can do that  by prepending it to the pattern, separated by space.
	 * For example, `'PUT post/<id:\d+>' => 'post/update'`.
	 * You may specify multiple verbs by separating them with comma
	 * like this: `'POST,PUT post/index' => 'post/create'`.
	 * The supported verbs in the shortcut format are: GET, HEAD, POST, PUT, PATCH and DELETE.
	 * Note that [[UrlRule::mode|mode]] will be set to PARSING_ONLY when specifying verb in this way
	 * so you normally would not specify a verb for normal GET request.
	 *
	 * Here is an example configuration for RESTful CRUD controller:
	 *
	 * ~~~php
	 * [
	 *     'dashboard' => 'site/index',
	 *
	 *     'POST <controller:\w+>s' => '<controller>/create',
	 *     '<controller:\w+>s' => '<controller>/index',
	 *
	 *     'PUT <controller:\w+>/<id:\d+>'    => '<controller>/update',
	 *     'DELETE <controller:\w+>/<id:\d+>' => '<controller>/delete',
	 *     '<controller:\w+>/<id:\d+>'        => '<controller>/view',
	 * ];
	 * ~~~
	 *
	 * Note that if you modify this property after the UrlManager object is created, make sure
	 * you populate the array with rule objects instead of rule configurations.
	 */
	public $rules = [];
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
	 * @var Cache|string the cache object or the application component ID of the cache object.
	 * Compiled URL rules will be cached through this cache object, if it is available.
	 *
	 * After the UrlManager object is created, if you want to change this property,
	 * you should only assign it with a cache object.
	 * Set this property to null if you do not want to cache the URL rules.
	 */
	public $cache = 'cache';
	/**
	 * @var array the default configuration of URL rules. Individual rule configurations
	 * specified via [[rules]] will take precedence when the same property of the rule is configured.
	 */
	public $ruleConfig = ['class' => 'yii\web\UrlRule'];

	private $_baseUrl;
	private $_hostInfo;

	/**
	 * Initializes UrlManager.
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
		if (!$this->enablePrettyUrl || empty($this->rules)) {
			return;
		}
		if (is_string($this->cache)) {
			$this->cache = Yii::$app->getComponent($this->cache);
		}
		if ($this->cache instanceof Cache) {
			$key = __CLASS__;
			$hash = md5(json_encode($this->rules));
			if (($data = $this->cache->get($key)) !== false && isset($data[1]) && $data[1] === $hash) {
				$this->rules = $data[0];
				return;
			}
		}

		$rules = [];
		foreach ($this->rules as $key => $rule) {
			if (!is_array($rule)) {
				$rule = ['route' => $rule];
				if (preg_match('/^((?:(GET|HEAD|POST|PUT|PATCH|DELETE),)*(GET|HEAD|POST|PUT|PATCH|DELETE))\s+(.*)$/', $key, $matches)) {
					$rule['verb'] = explode(',', $matches[1]);
					$rule['mode'] = UrlRule::PARSING_ONLY;
					$key = $matches[4];
				}
				$rule['pattern'] = $key;
			}
			$rules[] = Yii::createObject(array_merge($this->ruleConfig, $rule));
		}
		$this->rules = $rules;

		if (isset($key, $hash)) {
			$this->cache->set($key, [$this->rules, $hash]);
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
			$pathInfo = $request->getPathInfo();
			/** @var UrlRule $rule */
			foreach ($this->rules as $rule) {
				if (($result = $rule->parseRequest($this, $request)) !== false) {
					Yii::trace("Request parsed with URL rule: {$rule->name}", __METHOD__);
					return $result;
				}
			}

			if ($this->enableStrictParsing) {
				return false;
			}

			Yii::trace('No matching URL rules. Using default URL parsing logic.', __METHOD__);

			$suffix = (string)$this->suffix;
			if ($suffix !== '' && $pathInfo !== '') {
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

			return [$pathInfo, []];
		} else {
			Yii::trace('Pretty URL not enabled. Using default URL parsing logic.', __METHOD__);
			$route = $request->get($this->routeVar);
			if (is_array($route)) {
				$route = '';
			}
			return [(string)$route, []];
		}
	}

	/**
	 * Creates a URL using the given route and parameters.
	 * The URL created is a relative one. Use [[createAbsoluteUrl()]] to create an absolute URL.
	 * @param string $route the route
	 * @param array $params the parameters (name-value pairs)
	 * @return string the created URL
	 */
	public function createUrl($route, $params = [])
	{
		$anchor = isset($params['#']) ? '#' . $params['#'] : '';
		unset($params['#'], $params[$this->routeVar]);

		$route = trim($route, '/');
		$baseUrl = $this->getBaseUrl();

		if ($this->enablePrettyUrl) {
			/** @var UrlRule $rule */
			foreach ($this->rules as $rule) {
				if (($url = $rule->createUrl($this, $route, $params)) !== false) {
					if ($rule->host !== null) {
						if ($baseUrl !== '' && ($pos = strpos($url, '/', 8)) !== false) {
							return substr($url, 0, $pos) . $baseUrl . substr($url, $pos);
						} else {
							return $url . $baseUrl . $anchor;
						}
					} else {
						return "$baseUrl/{$url}{$anchor}";
					}
				}
			}

			if ($this->suffix !== null) {
				$route .= $this->suffix;
			}
			if (!empty($params)) {
				$route .= '?' . http_build_query($params);
			}
			return "$baseUrl/{$route}{$anchor}";
		} else {
			$url = "$baseUrl?{$this->routeVar}=$route";
			if (!empty($params)) {
				$url .= '&' . http_build_query($params);
			}
			return $url . $anchor;
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
	public function createAbsoluteUrl($route, $params = [])
	{
		$url = $this->createUrl($route, $params);
		if (strpos($url, '://') !== false) {
			return $url;
		} else {
			return $this->getHostInfo() . $url;
		}
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
			/** @var \yii\web\Request $request */
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
		$this->_baseUrl = rtrim($value, '/');
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
