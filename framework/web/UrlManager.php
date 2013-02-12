<?php
/**
 * UrlManager class file
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

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
	 * @var array the URL rules (pattern=>route).
	 */
	public $rules = array();
	/**
	 * @var string the URL suffix used when in 'path' format.
	 * For example, ".html" can be used so that the URL looks like pointing to a static HTML page. Defaults to empty.
	 */
	public $suffix;
	/**
	 * @var boolean whether to show entry script name in the constructed URL. Defaults to true.
	 */
	public $showScriptName = true;
	/**
	 * @var boolean whether to append GET parameters to the path info part. Defaults to true.
	 * This property is only effective when {@link urlFormat} is 'path' and is mainly used when
	 * creating URLs. When it is true, GET parameters will be appended to the path info and
	 * separate from each other using slashes. If this is false, GET parameters will be in query part.
	 */
	public $appendParams = true;
	/**
	 * @var string the GET variable name for route. Defaults to 'r'.
	 */
	public $routeVar = 'r';
	/**
	 * @var boolean whether routes are case-sensitive. Defaults to true. By setting this to false,
	 * the route in the incoming request will be turned to lower case first before further processing.
	 * As a result, you should follow the convention that you use lower case when specifying
	 * controller mapping ({@link CWebApplication::controllerMap}) and action mapping
	 * ({@link CController::actions}). Also, the directory names for organizing controllers should
	 * be in lower case.
	 */
	public $caseSensitive = true;
	/**
	 * @var boolean whether the GET parameter values should match the corresponding
	 * sub-patterns in a rule before using it to create a URL. Defaults to false, meaning
	 * a rule will be used for creating a URL only if its route and parameter names match the given ones.
	 * If this property is set true, then the given parameter values must also match the corresponding
	 * parameter sub-patterns. Note that setting this property to true will degrade performance.
	 * @since 1.1.0
	 */
	public $matchValue = false;
	/**
	 * @var string the ID of the cache application component that is used to cache the parsed URL rules.
	 * Defaults to 'cache' which refers to the primary cache application component.
	 * Set this property to false if you want to disable caching URL rules.
	 */
	public $cacheID = 'cache';
	/**
	 * @var boolean whether to enable strict URL parsing.
	 * This property is only effective when {@link urlFormat} is 'path'.
	 * If it is set true, then an incoming URL must match one of the {@link rules URL rules}.
	 * Otherwise, it will be treated as an invalid request and trigger a 404 HTTP exception.
	 * Defaults to false.
	 */
	public $useStrictParsing = false;
	/**
	 * @var string the class name or path alias for the URL rule instances. Defaults to 'CUrlRule'.
	 * If you change this to something else, please make sure that the new class must extend from
	 * {@link CBaseUrlRule} and have the same constructor signature as {@link CUrlRule}.
	 * It must also be serializable and autoloadable.
	 * @since 1.1.8
	 */
	public $urlRuleClass = 'CUrlRule';

	/**
	 * Initializes the application component.
	 */
	public function init()
	{
		parent::init();
		$this->processRules();
	}

	/**
	 * Processes the URL rules.
	 */
	protected function processRules()
	{
		foreach ($this->rules as $i => $rule) {
			if (!isset($rule['class'])) {
				$rule['class'] = 'yii\web\UrlRule';
			}
			$this->rules[$i] = \Yii::createObject($rule);
		}
	}

	/**
	 * Parses the user request.
	 * @param Request $request the request application component
	 * @return string the route (controllerID/actionID) and perhaps GET parameters in path format.
	 */
	public function parseUrl($request)
	{
	}

	public function createUrl($route, $params = array())
	{
		$anchor = isset($params['#']) ? '#' . $params['#'] : '';
		unset($anchor['#']);

		/** @var $rule UrlRule */
		foreach ($this->rules as $rule) {
			if (($url = $rule->createUrl($this, $route, $params)) !== false) {
				return $this->getBaseUrl() . $url . $anchor;
			}
		}

		if ($params !== array()) {
			$route .= '?' . http_build_query($params);
		}
		return $this->getBaseUrl() . '/' . $route . $anchor;
	}

	private $_baseUrl;

	public function getBaseUrl()
	{
		return $this->_baseUrl;
	}

	public function setBaseUrl($value)
	{
		$this->_baseUrl = trim($value, '/');
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
