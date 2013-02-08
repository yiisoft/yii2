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
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class UrlRule extends Object
{
	/**
	 * @var string the URL suffix used for this rule.
	 * For example, ".html" can be used so that the URL looks like pointing to a static HTML page.
	 * Defaults to null, meaning using the value of {@link CUrlManager::urlSuffix}.
	 */
	public $urlSuffix;
	/**
	 * @var boolean whether the rule is case sensitive. Defaults to null, meaning
	 * using the value of {@link CUrlManager::caseSensitive}.
	 */
	public $caseSensitive;
	/**
	 * @var array the default GET parameters (name=>value) that this rule provides.
	 * When this rule is used to parse the incoming request, the values declared in this property
	 * will be injected into $_GET.
	 */
	public $defaultParams = array();
	/**
	 * @var boolean whether the GET parameter values should match the corresponding
	 * sub-patterns in the rule when creating a URL. Defaults to null, meaning using the value
	 * of {@link CUrlManager::matchValue}. When this property is false, it means
	 * a rule will be used for creating a URL if its route and parameter names match the given ones.
	 * If this property is set true, then the given parameter values must also match the corresponding
	 * parameter sub-patterns. Note that setting this property to true will degrade performance.
	 * @since 1.1.0
	 */
	public $matchValue;
	/**
	 * @var string the HTTP verb (e.g. GET, POST, DELETE) that this rule should match.
	 * If this rule can match multiple verbs, please separate them with commas.
	 * If this property is not set, the rule can match any verb.
	 * Note that this property is only used when parsing a request. It is ignored for URL creation.
	 * @since 1.1.7
	 */
	public $verb;
	/**
	 * @var boolean whether this rule is only used for request parsing.
	 * Defaults to false, meaning the rule is used for both URL parsing and creation.
	 * @since 1.1.7
	 */
	public $parsingOnly = false;
	/**
	 * @var string the controller/action pair
	 */
	public $route;
	/**
	 * @var array the mapping from route param name to token name (e.g. _r1=><1>)
	 */
	public $references = array();
	/**
	 * @var string the pattern used to match route
	 */
	public $routePattern;
	/**
	 * @var string regular expression used to parse a URL
	 */
	public $pattern;
	/**
	 * @var string template used to construct a URL
	 */
	public $template;
	/**
	 * @var array list of parameters (name=>regular expression)
	 */
	public $params = array();
	/**
	 * @var boolean whether the URL allows additional parameters at the end of the path info.
	 */
	public $append;
	/**
	 * @var boolean whether host info should be considered for this rule
	 */
	public $hasHostInfo;
}
