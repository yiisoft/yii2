<?php
/**
 * UrlRule class file
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use yii\base\Object;

/**
 * UrlRule represents a rule used for parsing and generating URLs.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class UrlRule extends Object
{
	/**
	 * @var string regular expression used to parse a URL
	 */
	public $pattern;
	/**
	 * @var string the route to the controller action
	 */
	public $route;
	/**
	 * @var array the default GET parameters (name=>value) that this rule provides.
	 * When this rule is used to parse the incoming request, the values declared in this property
	 * will be injected into $_GET.
	 */
	public $defaults = array();
	/**
	 * @var boolean whether this rule is only used for request parsing.
	 * Defaults to false, meaning the rule is used for both URL parsing and creation.
	 */
	public $parsingOnly = false;
	/**
	 * @var string the URL suffix used for this rule.
	 * For example, ".html" can be used so that the URL looks like pointing to a static HTML page.
	 * If not, the value of [[UrlManager::suffix]] will be used.
	 */
	public $suffix;
	/**
	 * @var string|array the HTTP verb (e.g. GET, POST, DELETE) that this rule should match.
	 * Use array to represent multiple verbs that this rule may match.
	 * If this property is not set, the rule can match any verb.
	 * Note that this property is only used when parsing a request. It is ignored for URL creation.
	 * @see parsingOnly
	 */
	public $verb;
	/**
	 * @var string the host info (e.g. `http://www.example.com`) that this rule should match.
	 * If not set, it means the host info is ignored.
	 */
	public $hostInfo;

	/**
	 * @var string the template for generating a new URL. This is derived from [[pattern]] and is used in generating URL.
	 */
	private $_template;
	/**
	 * @var string the regex for matching the route part. This is used in generating URL.
	 */
	private $_routeRule;
	/**
	 * @var array list of regex for matching parameters. This is used in generating URL.
	 */
	private $_paramRules = array();
	/**
	 * @var array list of parameters used in the route.
	 */
	private $_routeParams = array();

	/**
	 * Initializes this rule.
	 */
	public function init()
	{
		if ($this->verb !== null) {
			if (is_array($this->verb)) {
				foreach ($this->verb as $i => $verb) {
					$this->verb[$i] = strtoupper($verb);
				}
			} else {
				$this->verb = array(strtoupper($this->verb));
			}
		}

		$this->pattern = trim($this->pattern, '/');
		if ($this->pattern === '') {
			$this->_template = '';
			$this->pattern = '#^$#u';
			return;
		} else {
			$this->pattern = '/' . $this->pattern . '/';
		}

		$this->route = trim($this->route, '/');
		if (strpos($this->route, '<') !== false && preg_match_all('/<(\w+)>/', $this->route, $matches)) {
			foreach ($matches[1] as $name) {
				$this->_routeParams[$name] = "<$name>";
			}
		}

		$tr = $tr2 = array();
		if (preg_match_all('/<(\w+):?([^>]+)?>/', $this->pattern, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
			foreach ($matches as $match) {
				$name = $match[1][0];
				$pattern = isset($match[2][0]) ? $match[2][0] : '[^\/]+';
				if (isset($this->defaults[$name])) {
					$length = strlen($match[0][0]);
					$offset = $match[0][1];
					if ($this->pattern[$offset - 1] === '/' && $this->pattern[$offset + $length] === '/') {
						$tr["/<$name>"] = "(/(?P<$name>$pattern))?";
					} else {
						$tr["<$name>"] = "(?P<$name>$pattern)?";
					}
				} else {
					$tr["<$name>"] = "(?P<$name>$pattern)";
				}
				if (isset($this->_routeParams[$name])) {
					$tr2["<$name>"] = "(?P<$name>$pattern)";
				} else {
					$this->_paramRules[$name] = $pattern === '[^\/]+' ? '' : "#^$pattern$#";
				}
			}
		}

		$this->_template = preg_replace('/<(\w+):?([^>]+)?>/', '<$1>', $this->pattern);
		$this->pattern = '#^' . trim(strtr($this->_template, $tr), '/') . '$#u';

		if ($this->_routeParams !== array()) {
			$this->_routeRule = '#^' . strtr($this->route, $tr2) . '$#u';
		}
	}

	public function parseUrl($pathInfo)
	{
		if ($this->verb !== null && !in_array(\Yii::$app->getRequest()->verb, $this->verb, true)) {
			return false;
		}

		if (!preg_match($this->pattern, $pathInfo, $matches)) {
			return false;
		}
		foreach ($this->defaults as $name => $value) {
			if (!isset($matches[$name]) || $matches[$name] === '') {
				$matches[$name] = $value;
			}
		}
		$params = $this->defaults;
		$tr = array();
		foreach ($matches as $name => $value) {
			if (isset($this->_routeParams[$name])) {
				$tr[$this->_routeParams[$name]] = $value;
				unset($params[$name]);
			} elseif (isset($this->_paramRules[$name])) {
				$params[$name] = $value;
			}
		}
		if ($this->_routeRule !== null) {
			$route = strtr($this->route, $tr);
		} else {
			$route = $this->route;
		}
		return array($route, $params);
	}

	public function createUrl($route, $params)
	{
		if ($this->parsingOnly) {
			return false;
		}

		$tr = array();

		// match the route part first
		if ($route !== $this->route) {
			if ($this->_routeRule !== null && preg_match($this->_routeRule, $route, $matches)) {
				foreach ($this->_routeParams as $name => $token) {
					if (isset($this->defaults[$name]) && strcmp($this->defaults[$name], $matches[$name]) === 0) {
						$tr[$token] = '';
					} else {
						$tr[$token] = $matches[$name];
					}
				}
			} else {
				return false;
			}
		}

		// match default params
		// if a default param is not in the route pattern, its value must also be matched
		foreach ($this->defaults as $name => $value) {
			if (isset($this->_routeParams[$name])) {
				continue;
			}
			if (!isset($params[$name])) {
				return false;
			} elseif (strcmp($params[$name], $value) === 0) { // strcmp will do string conversion automatically
				unset($params[$name]);
				if (isset($this->_paramRules[$name])) {
					$tr["<$name>"] = '';
				}
			} elseif (!isset($this->_paramRules[$name])) {
				return false;
			}
		}

		// match params in the pattern
		foreach ($this->_paramRules as $name => $rule) {
			if (isset($params[$name]) && ($rule === '' || preg_match($rule, $params[$name]))) {
				$tr["<$name>"] = urlencode($params[$name]);
				unset($params[$name]);
			} elseif (!isset($this->defaults[$name]) || isset($params[$name])) {
				return false;
			}
		}

		$url = trim(strtr($this->_template, $tr), '/');
		if (strpos($url, '//') !== false) {
			$url = preg_replace('#/+#', '/', $url);
		}
		if ($params !== array()) {
			$url .= '?' . http_build_query($params);
		}
		return $url;
	}
}
