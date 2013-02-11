<?php
/**
 * UrlManager class file
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use yii\base\Object;

/**
 * UrlManager manages the URLs of Yii applications.
 * array(
 *     'pattern' => 'post/<page:\d+>',
 *     'route' => 'post/view',
 *     'defaults' => array('page' => 1),
 * )
 *
 * array(
 *     'pattern' => 'about',
 *     'route' => 'site/page',
 *     'defaults' => array('view' => 'about'),
 * )
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

	protected $paramRules = array();
	protected $routeRule;
	protected $template;
	protected $routeParams = array();

	public function init()
	{
		$this->pattern = trim($this->pattern, '/');
		if ($this->pattern === '') {
			$this->template = '';
			$this->pattern = '#^$#u';
			return;
		} else {
			$this->pattern = '/' . $this->pattern . '/';
		}

		$this->route = trim($this->route, '/');
		if (strpos($this->route, '<') !== false && preg_match_all('/<(\w+)>/', $this->route, $matches)) {
			foreach ($matches[1] as $name) {
				$this->routeParams[$name] = "<$name>";
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
						$tr["<$name>"] = "(?P<$name>(?:/$pattern)?)";
					} else {
						$tr["<$name>"] = "(?P<$name>(?:$pattern)?)";
					}
				} else {
					$tr["<$name>"] = "(?P<$name>$pattern)";
				}
				if (isset($this->routeParams[$name])) {
					$tr2["<$name>"] = "(?P<$name>$pattern)";
				} else {
					$this->paramRules[$name] = $pattern === '[^\/]+' ? '' : "#^$pattern$#";
				}
			}
		}

		$this->template = preg_replace('/<(\w+):?([^>]+)?>/', '<$1>', $this->pattern);
		$this->pattern = '#^' . strtr($this->template, $tr) . '$#u';

		if ($this->routeParams !== array()) {
			$this->routeRule = '#^' . strtr($this->route, $tr2) . '$#u';
		}
	}

	public function parseUrl($pathInfo)
	{

	}

	public function createUrl($route, $params)
	{
		$tr = array();

		// match the route part first
		if ($route !== $this->route) {
			if ($this->routeRule !== null && preg_match($this->routeRule, $route, $matches)) {
				foreach ($this->routeParams as $name => $token) {
					if (isset($this->defaults[$name]) && strcmp($this->defaults[$name], $matches[$name]) === 0) {
						$tr[$token] = '';
						$tr["/$token/"] = '/';
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
			if (isset($this->routeParams[$name])) {
				continue;
			}
			if (!isset($params[$name])) {
				return false;
			} elseif (strcmp($params[$name], $value) === 0) { // strcmp will do string conversion automatically
				unset($params[$name]);
				if (isset($this->paramRules[$name])) {
					$tr["<$name>"] = '';
					$tr["/<$name>/"] = '/';
				}
			} elseif (!isset($this->paramRules[$name])) {
				return false;
			}
		}

		// match params in the pattern
		foreach ($this->paramRules as $name => $rule) {
			if (isset($params[$name]) && ($rule === '' || preg_match($rule, $params[$name]))) {
				$tr["<$name>"] = urlencode($params[$name]);
				unset($params[$name]);
			} elseif (!isset($this->defaults[$name]) || isset($params[$name])) {
				return false;
			}
		}

		$url = trim(strtr($this->template, $tr), '/');
		if (strpos($url, '//') !== false) {
			$url = preg_replace('#/+#', '/', $url);
		}
		if ($params !== array()) {
			$url .= '?' . http_build_query($params);
		}
		return $url;
	}

	public function parse($pathInfo)
	{
		$route = '';
		$params = array();
		return array($route, $params);
	}
}
