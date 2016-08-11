<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\Object;
use yii\base\InvalidConfigException;

/**
 * UrlRule represents a rule used by [[UrlManager]] for parsing and generating URLs.
 *
 * To define your own URL parsing and creation logic you can extend from this class
 * and add it to [[UrlManager::rules]] like this:
 *
 * ```php
 * 'rules' => [
 *     ['class' => 'MyUrlRule', 'pattern' => '...', 'route' => 'site/index', ...],
 *     // ...
 * ]
 * ```
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class UrlRule extends Object implements UrlRuleInterface
{
    /**
     * Set [[mode]] with this value to mark that this rule is for URL parsing only
     */
    const PARSING_ONLY = 1;
    /**
     * Set [[mode]] with this value to mark that this rule is for URL creation only
     */
    const CREATION_ONLY = 2;

    /**
     * @var string the name of this rule. If not set, it will use [[pattern]] as the name.
     */
    public $name;
    /**
     * On the rule initialization, the [[pattern]] matching parameters names will be replaced with [[placeholders]].
     * @var string the pattern used to parse and create the path info part of a URL.
     * @see host
     * @see placeholders
     */
    public $pattern;
    /**
     * @var string the pattern used to parse and create the host info part of a URL (e.g. `http://example.com`).
     * @see pattern
     */
    public $host;
    /**
     * @var string the route to the controller action
     */
    public $route;
    /**
     * @var array the default GET parameters (name => value) that this rule provides.
     * When this rule is used to parse the incoming request, the values declared in this property
     * will be injected into $_GET.
     */
    public $defaults = [];
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
     */
    public $verb;
    /**
     * @var integer a value indicating if this rule should be used for both request parsing and URL creation,
     * parsing only, or creation only.
     * If not set or 0, it means the rule is both request parsing and URL creation.
     * If it is [[PARSING_ONLY]], the rule is for request parsing only.
     * If it is [[CREATION_ONLY]], the rule is for URL creation only.
     */
    public $mode;
    /**
     * @var boolean a value indicating if parameters should be url encoded.
     */
    public $encodeParams = true;

    /**
     * @var array|string normalize configuration
     */
    public $normalize;

    /**
     * @var array list of placeholders for matching parameters names. Used in [[parseRequest()]], [[createUrl()]].
     * On the rule initialization, the [[pattern]] parameters names will be replaced with placeholders.
     * This array contains relations between the original parameters names and their placeholders.
     * The array keys are the placeholders and the values are the original names.
     *
     * @see parseRequest()
     * @see createUrl()
     * @since 2.0.7
     */
    protected $placeholders = [];

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
    private $_paramRules = [];
    /**
     * @var array list of parameters used in the route.
     */
    private $_routeParams = [];


    /**
     * Initializes this rule.
     */
    public function init()
    {
        if ($this->pattern === null) {
            throw new InvalidConfigException('UrlRule::pattern must be set.');
        }
        if ($this->route === null) {
            throw new InvalidConfigException('UrlRule::route must be set.');
        }
        if ($this->verb !== null) {
            if (is_array($this->verb)) {
                foreach ($this->verb as $i => $verb) {
                    $this->verb[$i] = strtoupper($verb);
                }
            } else {
                $this->verb = [strtoupper($this->verb)];
            }
        }
        if ($this->name === null) {
            $this->name = $this->pattern;
        }

        $this->pattern = trim($this->pattern, '/');
        $this->route = trim($this->route, '/');

        if ($this->host !== null) {
            $this->host = rtrim($this->host, '/');
            $this->pattern = rtrim($this->host . '/' . $this->pattern, '/');
        } elseif ($this->pattern === '') {
            $this->_template = '';
            $this->pattern = '#^$#u';

            return;
        } elseif (($pos = strpos($this->pattern, '://')) !== false) {
            if (($pos2 = strpos($this->pattern, '/', $pos + 3)) !== false) {
                $this->host = substr($this->pattern, 0, $pos2);
            } else {
                $this->host = $this->pattern;
            }
        } else {
            $this->pattern = '/' . $this->pattern . '/';
        }

        if (strpos($this->route, '<') !== false && preg_match_all('/<([\w._-]+)>/', $this->route, $matches)) {
            foreach ($matches[1] as $name) {
                $this->_routeParams[$name] = "<$name>";
            }
        }

        $tr = [
            '.' => '\\.',
            '*' => '\\*',
            '$' => '\\$',
            '[' => '\\[',
            ']' => '\\]',
            '(' => '\\(',
            ')' => '\\)',
        ];

        $tr2 = [];
        if (preg_match_all('/<([\w._-]+):?([^>]+)?>/', $this->pattern, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $name = $match[1][0];
                $pattern = isset($match[2][0]) ? $match[2][0] : '[^\/]+';
                $placeholder = 'a' . hash('crc32b', $name); // placeholder must begin with a letter
                $this->placeholders[$placeholder] = $name;
                if (array_key_exists($name, $this->defaults)) {
                    $length = strlen($match[0][0]);
                    $offset = $match[0][1];
                    if ($offset > 1 && $this->pattern[$offset - 1] === '/' && (!isset($this->pattern[$offset + $length]) || $this->pattern[$offset + $length] === '/')) {
                        $tr["/<$name>"] = "(/(?P<$placeholder>$pattern))?";
                    } else {
                        $tr["<$name>"] = "(?P<$placeholder>$pattern)?";
                    }
                } else {
                    $tr["<$name>"] = "(?P<$placeholder>$pattern)";
                }
                if (isset($this->_routeParams[$name])) {
                    $tr2["<$name>"] = "(?P<$placeholder>$pattern)";
                } else {
                    $this->_paramRules[$name] = $pattern === '[^\/]+' ? '' : "#^$pattern$#u";
                }
            }
        }

        $this->_template = preg_replace('/<([\w._-]+):?([^>]+)?>/', '<$1>', $this->pattern);
        $this->pattern = '#^' . trim(strtr($this->_template, $tr), '/') . '$#u';

        if (!empty($this->_routeParams)) {
            $this->_routeRule = '#^' . strtr($this->route, $tr2) . '$#u';
        }
    }

    /**
     * Initializes normalizer configuration
     * @param UrlManager $manager the URL manager
     * @throws \Exception if config is not understood
     */
    protected function initNormalizer($manager) {
        $normalize = $this->normalize;

        $configNoAction = [
            // whether to collapse multiple slashes to single
            'collapse-slashes' => false,
            // add or remove trailing slash?
            // true - add
            // false - remove
            // null - do neither
            'trailing-slash' => null,
            // what action to perform if pathInfo was changed during normalization
            // redirect: instant redirect to normalized path
            // route: return new route (must be configured via 'route')
            // none: no action, just route by normalized path
            'action' => 'none',
            // string: route for $action == 'route', i.e. 'site/redirect'
            'route' => null
        ];

        if (is_string($normalize) || is_null($normalize)) {
            switch ($normalize) {
                case '':
                case 'safe-remove-trailing-slash':
                    // Default behavior.
                    // Removes trailing slash only if $suffix != '/'.
                    // Redirects 'posts/' to 'posts' and 'posts.html/' to 'posts.html'
                    // See issue #6498 for details.
                    // Discussion: https://github.com/yiisoft/yii2/pull/11381#discussion_r60205774
                    $suffix = (string)($this->suffix === null ? $manager->suffix : $this->suffix);
                    $normalize = [
                        'collapse-slashes' => false,
                        // if $suffix != '/', set normalizer to remove trailing slash, otherwise don't act.
                        'trailing-slash' => ($suffix != '/') ? false : null,
                        'action' => 'redirect'
                    ];
                    break;
                case 'disabled':
                    $normalize = $configNoAction;
                    break;
                case 'add-trailing-slash':
                    $normalize = [
                        'collapse-slashes' => true,
                        'trailing-slash' => true,
                        'action' => 'redirect'
                    ];
                    break;
                case 'remove-trailing-slash':
                    $normalize = [
                        'collapse-slashes' => true,
                        'trailing-slash' => false,
                        'action' => 'redirect'
                    ];
                    break;
                default:
                    throw new \Exception('Unknown normalization strategy ' . $normalize);
            }
        }

        $normalize = array_merge($configNoAction, $normalize);

        $this->normalize = $normalize;

        // set $this->suffix if trailing slash is required (for createUrl())
        if ($this->normalize['trailing-slash']) {
            $this->suffix = '/';
        }
    }

    /**
     * Normalizes supplied pathInfo by normalization rules.
     *
     * @param $pathInfo string original pathInfo
     * @return string normalized pathInfo
     */
    protected function normalizePathInfo($pathInfo) {
        $normalize = $this->normalize;

        if (!$pathInfo) {
            return $pathInfo; // nothing to normalize
        }

        if ($normalize['collapse-slashes']) {
            $pathInfo = ltrim(preg_replace('#/+#', '/', $pathInfo), '/');
        }

        if (!is_null($normalize['trailing-slash'])) {
            if ($normalize['trailing-slash']) {
                // add trailing slash
                if (substr($pathInfo, -1) != '/') {
                    $pathInfo .= '/';
                }
            } else {
                // remove trailing slash
                if (substr($pathInfo, -1) == '/') {
                    $pathInfo = rtrim($pathInfo, '/');
                }
            }
        }

        return $pathInfo;
    }

    protected function normalize($pathInfoOrig, $pathInfoNormalized, $origRoute) {
        // check whether pathInfo was changed during normalization.
        // If so, take the required action.
        if ($pathInfoNormalized != $pathInfoOrig) {
            switch ($this->normalize['action']) {
                case 'redirect':
                    $e = new NormalizerActionException("Request should be normalized");
                    $e->setAction('redirect');
                    $e->setRedirectUrl($pathInfoNormalized);
                    $e->setOrigPathInfo($pathInfoOrig);
                    $e->setOrigRoute($origRoute);
                    throw $e;
                    break;
                case 'route':
                    // construct new route
                    $redirectRoute = $this->normalize['route'];
                    $redirectParams = [
                        'redirectUrl' => $pathInfoNormalized,
                        'origRoute' => $origRoute,
                        'origPathInfo' => $pathInfoOrig,
                    ];
                    return [$redirectRoute, $redirectParams];
                    break;
                case 'none':
                    // no action
                    return $origRoute;
                default:
                    throw new \Exception("Unknown normalizer action " . $this->normalize['action']);
                    break;
            }
        }

        return $origRoute;
    }

    /**
     * Parses the given request and returns the corresponding route and parameters.
     * @param UrlManager $manager the URL manager
     * @param Request $request the request component
     * @return array|boolean the parsing result. The route and the parameters are returned as an array.
     * If false, it means this rule cannot be used to parse this path info.
     */
    public function parseRequest($manager, $request)
    {
        if ($this->mode === self::CREATION_ONLY) {
            return false;
        }

        if (!empty($this->verb) && !in_array($request->getMethod(), $this->verb, true)) {
            return false;
        }

        // init normalizer, as it may depend on UrlManager $suffix
        $this->initNormalizer($manager);

        $pathInfoOrig = $request->getPathInfo();
        $pathInfoNormalized = $this->normalizePathInfo($request->getPathInfo());
        $pathInfo = $pathInfoNormalized;

        $suffix = (string)($this->suffix === null ? $manager->suffix : $this->suffix);
        if ($suffix !== '' && $pathInfo !== '') {
            $n = strlen($suffix);
            if (substr_compare($pathInfo, $suffix, -$n, $n) === 0) {
                $pathInfo = substr($pathInfo, 0, -$n);
                if ($pathInfo === '') {
                    // suffix alone is not allowed
                    return false;
                }
            } else {
                return false;
            }
        }

        if ($this->host !== null) {
            $pathInfo = strtolower($request->getHostInfo()) . ($pathInfo === '' ? '' : '/' . $pathInfo);
        }

        if (!preg_match($this->pattern, $pathInfo, $matches)) {
            return false;
        }

        $matches = $this->substitutePlaceholderNames($matches);
        foreach ($this->defaults as $name => $value) {
            if (!isset($matches[$name]) || $matches[$name] === '') {
                $matches[$name] = $value;
            }
        }
        $params = $this->defaults;
        $tr = [];
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

        Yii::trace("Request parsed with URL rule: {$this->name}", __METHOD__);

        return $this->normalize($pathInfoOrig, $pathInfoNormalized, [$route, $params]);
    }

    /**
     * Creates a URL according to the given route and parameters.
     * @param UrlManager $manager the URL manager
     * @param string $route the route. It should not have slashes at the beginning or the end.
     * @param array $params the parameters
     * @return string|boolean the created URL, or false if this rule cannot be used for creating this URL.
     */
    public function createUrl($manager, $route, $params)
    {
        if ($this->mode === self::PARSING_ONLY) {
            return false;
        }

        // init normalizer, because it may change $this->suffix according to chosen strategy
        $this->initNormalizer($manager);

        $tr = [];

        // match the route part first
        if ($route !== $this->route) {
            if ($this->_routeRule !== null && preg_match($this->_routeRule, $route, $matches)) {
                $matches = $this->substitutePlaceholderNames($matches);
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
            if (isset($params[$name]) && !is_array($params[$name]) && ($rule === '' || preg_match($rule, $params[$name]))) {
                $tr["<$name>"] = $this->encodeParams ? urlencode($params[$name]) : $params[$name];
                unset($params[$name]);
            } elseif (!isset($this->defaults[$name]) || isset($params[$name])) {
                return false;
            }
        }

        $url = trim(strtr($this->_template, $tr), '/');
        if ($this->host !== null) {
            $pos = strpos($url, '/', 8);
            if ($pos !== false) {
                $url = substr($url, 0, $pos) . preg_replace('#/+#', '/', substr($url, $pos));
            }
        } elseif (strpos($url, '//') !== false) {
            $url = preg_replace('#/+#', '/', $url);
        }

        if ($url !== '') {
            $url .= ($this->suffix === null ? $manager->suffix : $this->suffix);
        }

        if (!empty($params) && ($query = http_build_query($params)) !== '') {
            $url .= '?' . $query;
        }

        return $url;
    }

    /**
     * Returns list of regex for matching parameter.
     * @return array parameter keys and regexp rules.
     *
     * @since 2.0.6
     */
    protected function getParamRules()
    {
        return $this->_paramRules;
    }

    /**
     * Iterates over [[placeholders]] and checks whether each placeholder exists as a key in $matches array.
     * When found - replaces this placeholder key with a appropriate name of matching parameter.
     * Used in [[parseRequest()]], [[createUrl()]].
     *
     * @param array $matches result of `preg_match()` call
     * @return array input array with replaced placeholder keys
     * @see placeholders
     * @since 2.0.7
     */
    protected function substitutePlaceholderNames(array $matches)
    {
        foreach ($this->placeholders as $placeholder => $name) {
            if (isset($matches[$placeholder])) {
                $matches[$name] = $matches[$placeholder];
                unset($matches[$placeholder]);
            }
        }
        return $matches;
    }
}
