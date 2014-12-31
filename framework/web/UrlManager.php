<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\caching\Cache;

/**
 * UrlManager handles HTTP request parsing and creation of URLs based on a set of rules.
 *
 * UrlManager is configured as an application component in [[\yii\base\Application]] by default.
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
 * @property string $baseUrl The base URL that is used by [[createUrl()]] to prepend to created URLs.
 * @property string $hostInfo The host info (e.g. "http://www.example.com") that is used by
 * [[createAbsoluteUrl()]] to prepend to created URLs.
 * @property string $scriptUrl The entry script URL that is used by [[createUrl()]] to prepend to created
 * URLs.
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
     * @var string the GET parameter name for route. This property is used only if [[enablePrettyUrl]] is false.
     */
    public $routeParam = 'r';
    /**
     * @var Cache|string the cache object or the application component ID of the cache object.
     * Compiled URL rules will be cached through this cache object, if it is available.
     *
     * After the UrlManager object is created, if you want to change this property,
     * you should only assign it with a cache object.
     * Set this property to false if you do not want to cache the URL rules.
     */
    public $cache = 'cache';
    /**
     * @var array the default configuration of URL rules. Individual rule configurations
     * specified via [[rules]] will take precedence when the same property of the rule is configured.
     */
    public $ruleConfig = ['class' => 'yii\web\UrlRule'];

    private $_baseUrl;
    private $_scriptUrl;
    private $_hostInfo;


    /**
     * Initializes UrlManager.
     */
    public function init()
    {
        parent::init();

        if (!$this->enablePrettyUrl || empty($this->rules)) {
            return;
        }
        if (is_string($this->cache)) {
            $this->cache = Yii::$app->get($this->cache, false);
        }
        if ($this->cache instanceof Cache) {
            $cacheKey = __CLASS__;
            $hash = md5(json_encode($this->rules));
            if (($data = $this->cache->get($cacheKey)) !== false && isset($data[1]) && $data[1] === $hash) {
                $this->rules = $data[0];
            } else {
                $this->rules = $this->buildRules($this->rules);
                $this->cache->set($cacheKey, [$this->rules, $hash]);
            }
        } else {
            $this->rules = $this->buildRules($this->rules);
        }
    }

    /**
     * Adds additional URL rules.
     *
     * This method will call [[buildRules()]] to parse the given rule declarations and then append or insert
     * them to the existing [[rules]].
     *
     * Note that if [[enablePrettyUrl]] is false, this method will do nothing.
     *
     * @param array $rules the new rules to be added. Each array element represents a single rule declaration.
     * Please refer to [[rules]] for the acceptable rule format.
     * @param boolean $append whether to add the new rules by appending them to the end of the existing rules.
     */
    public function addRules($rules, $append = true)
    {
        if (!$this->enablePrettyUrl) {
            return;
        }
        $rules = $this->buildRules($rules);
        if ($append) {
            $this->rules = array_merge($this->rules, $rules);
        } else {
            $this->rules = array_merge($rules, $this->rules);
        }
    }

    /**
     * Builds URL rule objects from the given rule declarations.
     * @param array $rules the rule declarations. Each array element represents a single rule declaration.
     * Please refer to [[rules]] for the acceptable rule formats.
     * @return UrlRuleInterface[] the rule objects built from the given rule declarations
     * @throws InvalidConfigException if a rule declaration is invalid
     */
    protected function buildRules($rules)
    {
        $compiledRules = [];
        $verbs = 'GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS';
        foreach ($rules as $key => $rule) {
            if (is_string($rule)) {
                $rule = ['route' => $rule];
                if (preg_match("/^((?:($verbs),)*($verbs))\\s+(.*)$/", $key, $matches)) {
                    $rule['verb'] = explode(',', $matches[1]);
                    if (!in_array('GET', $rule['verb'])) {
                        $rule['mode'] = UrlRule::PARSING_ONLY;
                    }
                    $key = $matches[4];
                }
                $rule['pattern'] = $key;
            }
            if (is_array($rule)) {
                $rule = Yii::createObject(array_merge($this->ruleConfig, $rule));
            }
            if (!$rule instanceof UrlRuleInterface) {
                throw new InvalidConfigException('URL rule class must implement UrlRuleInterface.');
            }
            $compiledRules[] = $rule;
        }
        return $compiledRules;
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
            /* @var $rule UrlRule */
            foreach ($this->rules as $rule) {
                if (($result = $rule->parseRequest($this, $request)) !== false) {
                    return $result;
                }
            }

            if ($this->enableStrictParsing) {
                return false;
            }

            Yii::trace('No matching URL rules. Using default URL parsing logic.', __METHOD__);

            $suffix = (string) $this->suffix;
            if ($suffix !== '' && $pathInfo !== '') {
                $n = strlen($this->suffix);
                if (substr_compare($pathInfo, $this->suffix, -$n, $n) === 0) {
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
            $route = $request->getQueryParam($this->routeParam, '');
            if (is_array($route)) {
                $route = '';
            }

            return [(string) $route, []];
        }
    }

    /**
     * Creates a URL using the given route and query parameters.
     *
     * You may specify the route as a string, e.g., `site/index`. You may also use an array
     * if you want to specify additional query parameters for the URL being created. The
     * array format must be:
     *
     * ```php
     * // generates: /index.php?r=site/index&param1=value1&param2=value2
     * ['site/index', 'param1' => 'value1', 'param2' => 'value2']
     * ```
     *
     * If you want to create a URL with an anchor, you can use the array format with a `#` parameter.
     * For example,
     *
     * ```php
     * // generates: /index.php?r=site/index&param1=value1#name
     * ['site/index', 'param1' => 'value1', '#' => 'name']
     * ```
     *
     * The URL created is a relative one. Use [[createAbsoluteUrl()]] to create an absolute URL.
     *
     * Note that unlike [[\yii\helpers\Url::toRoute()]], this method always treats the given route
     * as an absolute route.
     *
     * @param string|array $params use a string to represent a route (e.g. `site/index`),
     * or an array to represent a route with query parameters (e.g. `['site/index', 'param1' => 'value1']`).
     * @return string the created URL
     */
    public function createUrl($params)
    {
        $params = (array) $params;
        $anchor = isset($params['#']) ? '#' . $params['#'] : '';
        unset($params['#'], $params[$this->routeParam]);

        $route = trim($params[0], '/');
        unset($params[0]);

        $baseUrl = $this->showScriptName || !$this->enablePrettyUrl ? $this->getScriptUrl() : $this->getBaseUrl();

        if ($this->enablePrettyUrl) {
            /* @var $rule UrlRule */
            foreach ($this->rules as $rule) {
                if (($url = $rule->createUrl($this, $route, $params)) !== false) {
                    if (strpos($url, '://') !== false) {
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
            if (!empty($params) && ($query = http_build_query($params)) !== '') {
                $route .= '?' . $query;
            }

            return "$baseUrl/{$route}{$anchor}";
        } else {
            $url = "$baseUrl?{$this->routeParam}=" . urlencode($route);
            if (!empty($params) && ($query = http_build_query($params)) !== '') {
                $url .= '&' . $query;
            }

            return $url . $anchor;
        }
    }

    /**
     * Creates an absolute URL using the given route and query parameters.
     *
     * This method prepends the URL created by [[createUrl()]] with the [[hostInfo]].
     *
     * Note that unlike [[\yii\helpers\Url::toRoute()]], this method always treats the given route
     * as an absolute route.
     *
     * @param string|array $params use a string to represent a route (e.g. `site/index`),
     * or an array to represent a route with query parameters (e.g. `['site/index', 'param1' => 'value1']`).
     * @param string $scheme the scheme to use for the url (either `http` or `https`). If not specified
     * the scheme of the current request will be used.
     * @return string the created URL
     * @see createUrl()
     */
    public function createAbsoluteUrl($params, $scheme = null)
    {
        $params = (array) $params;
        $url = $this->createUrl($params);
        if (strpos($url, '://') === false) {
            $url = $this->getHostInfo() . $url;
        }
        if (is_string($scheme) && ($pos = strpos($url, '://')) !== false) {
            $url = $scheme . substr($url, $pos);
        }

        return $url;
    }

    /**
     * Returns the base URL that is used by [[createUrl()]] to prepend to created URLs.
     * It defaults to [[Request::baseUrl]].
     * This is mainly used when [[enablePrettyUrl]] is true and [[showScriptName]] is false.
     * @return string the base URL that is used by [[createUrl()]] to prepend to created URLs.
     * @throws InvalidConfigException if running in console application and [[baseUrl]] is not configured.
     */
    public function getBaseUrl()
    {
        if ($this->_baseUrl === null) {
            $request = Yii::$app->getRequest();
            if ($request instanceof Request) {
                $this->_baseUrl = $request->getBaseUrl();
            } else {
                throw new InvalidConfigException('Please configure UrlManager::baseUrl correctly as you are running a console application.');
            }
        }

        return $this->_baseUrl;
    }

    /**
     * Sets the base URL that is used by [[createUrl()]] to prepend to created URLs.
     * This is mainly used when [[enablePrettyUrl]] is true and [[showScriptName]] is false.
     * @param string $value the base URL that is used by [[createUrl()]] to prepend to created URLs.
     */
    public function setBaseUrl($value)
    {
        $this->_baseUrl = rtrim($value, '/');
    }

    /**
     * Returns the entry script URL that is used by [[createUrl()]] to prepend to created URLs.
     * It defaults to [[Request::scriptUrl]].
     * This is mainly used when [[enablePrettyUrl]] is false or [[showScriptName]] is true.
     * @return string the entry script URL that is used by [[createUrl()]] to prepend to created URLs.
     * @throws InvalidConfigException if running in console application and [[scriptUrl]] is not configured.
     */
    public function getScriptUrl()
    {
        if ($this->_scriptUrl === null) {
            $request = Yii::$app->getRequest();
            if ($request instanceof Request) {
                $this->_scriptUrl = $request->getScriptUrl();
            } else {
                throw new InvalidConfigException('Please configure UrlManager::scriptUrl correctly as you are running a console application.');
            }
        }

        return $this->_scriptUrl;
    }

    /**
     * Sets the entry script URL that is used by [[createUrl()]] to prepend to created URLs.
     * This is mainly used when [[enablePrettyUrl]] is false or [[showScriptName]] is true.
     * @param string $value the entry script URL that is used by [[createUrl()]] to prepend to created URLs.
     */
    public function setScriptUrl($value)
    {
        $this->_scriptUrl = $value;
    }

    /**
     * Returns the host info that is used by [[createAbsoluteUrl()]] to prepend to created URLs.
     * @return string the host info (e.g. "http://www.example.com") that is used by [[createAbsoluteUrl()]] to prepend to created URLs.
     * @throws InvalidConfigException if running in console application and [[hostInfo]] is not configured.
     */
    public function getHostInfo()
    {
        if ($this->_hostInfo === null) {
            $request = Yii::$app->getRequest();
            if ($request instanceof \yii\web\Request) {
                $this->_hostInfo = $request->getHostInfo();
            } else {
                throw new InvalidConfigException('Please configure UrlManager::hostInfo correctly as you are running a console application.');
            }
        }

        return $this->_hostInfo;
    }

    /**
     * Sets the host info that is used by [[createAbsoluteUrl()]] to prepend to created URLs.
     * @param string $value the host info (e.g. "http://www.example.com") that is used by [[createAbsoluteUrl()]] to prepend to created URLs.
     */
    public function setHostInfo($value)
    {
        $this->_hostInfo = rtrim($value, '/');
    }
}
