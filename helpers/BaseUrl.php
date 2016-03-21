<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

use Yii;
use yii\base\InvalidParamException;

/**
 * BaseUrl provides concrete implementation for [[Url]].
 *
 * Do not use BaseUrl. Use [[Url]] instead.
 *
 * @author Alexander Makarov <sam@rmcreative.ru>
 * @since 2.0
 */
class BaseUrl
{
    /**
     * @var \yii\web\UrlManager URL manager to use for creating URLs
     * @since 2.0.8
     */
    public static $urlManager;


    /**
     * Creates a URL for the given route.
     *
     * This method will use [[\yii\web\UrlManager]] to create a URL.
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
     * A route may be either absolute or relative. An absolute route has a leading slash (e.g. `/site/index`),
     * while a relative route has none (e.g. `site/index` or `index`). A relative route will be converted
     * into an absolute one by the following rules:
     *
     * - If the route is an empty string, the current [[\yii\web\Controller::route|route]] will be used;
     * - If the route contains no slashes at all (e.g. `index`), it is considered to be an action ID
     *   of the current controller and will be prepended with [[\yii\web\Controller::uniqueId]];
     * - If the route has no leading slash (e.g. `site/index`), it is considered to be a route relative
     *   to the current module and will be prepended with the module's [[\yii\base\Module::uniqueId|uniqueId]].
     *
     * Starting from version 2.0.2, a route can also be specified as an alias. In this case, the alias
     * will be converted into the actual route first before conducting the above transformation steps.
     *
     * Below are some examples of using this method:
     *
     * ```php
     * // /index.php?r=site%2Findex
     * echo Url::toRoute('site/index');
     *
     * // /index.php?r=site%2Findex&src=ref1#name
     * echo Url::toRoute(['site/index', 'src' => 'ref1', '#' => 'name']);
     *
     * // http://www.example.com/index.php?r=site%2Findex
     * echo Url::toRoute('site/index', true);
     *
     * // https://www.example.com/index.php?r=site%2Findex
     * echo Url::toRoute('site/index', 'https');
     *
     * // /index.php?r=post%2Findex     assume the alias "@posts" is defined as "post/index"
     * echo Url::toRoute('@posts');
     * ```
     *
     * @param string|array $route use a string to represent a route (e.g. `index`, `site/index`),
     * or an array to represent a route with query parameters (e.g. `['site/index', 'param1' => 'value1']`).
     * @param boolean|string $scheme the URI scheme to use in the generated URL:
     *
     * - `false` (default): generating a relative URL.
     * - `true`: returning an absolute base URL whose scheme is the same as that in [[\yii\web\UrlManager::hostInfo]].
     * - string: generating an absolute URL with the specified scheme (either `http` or `https`).
     *
     * @return string the generated URL
     * @throws InvalidParamException a relative route is given while there is no active controller
     */
    public static function toRoute($route, $scheme = false)
    {
        $route = (array) $route;
        $route[0] = static::normalizeRoute($route[0]);

        if ($scheme) {
            return static::getUrlManager()->createAbsoluteUrl($route, is_string($scheme) ? $scheme : null);
        } else {
            return static::getUrlManager()->createUrl($route);
        }
    }

    /**
     * Normalizes route and makes it suitable for UrlManager. Absolute routes are staying as is
     * while relative routes are converted to absolute ones.
     *
     * A relative route is a route without a leading slash, such as "view", "post/view".
     *
     * - If the route is an empty string, the current [[\yii\web\Controller::route|route]] will be used;
     * - If the route contains no slashes at all, it is considered to be an action ID
     *   of the current controller and will be prepended with [[\yii\web\Controller::uniqueId]];
     * - If the route has no leading slash, it is considered to be a route relative
     *   to the current module and will be prepended with the module's uniqueId.
     *
     * Starting from version 2.0.2, a route can also be specified as an alias. In this case, the alias
     * will be converted into the actual route first before conducting the above transformation steps.
     *
     * @param string $route the route. This can be either an absolute route or a relative route.
     * @return string normalized route suitable for UrlManager
     * @throws InvalidParamException a relative route is given while there is no active controller
     */
    protected static function normalizeRoute($route)
    {
        $route = Yii::getAlias((string) $route);
        if (strncmp($route, '/', 1) === 0) {
            // absolute route
            return ltrim($route, '/');
        }

        // relative route
        if (Yii::$app->controller === null) {
            throw new InvalidParamException("Unable to resolve the relative route: $route. No active controller is available.");
        }

        if (strpos($route, '/') === false) {
            // empty or an action ID
            return $route === '' ? Yii::$app->controller->getRoute() : Yii::$app->controller->getUniqueId() . '/' . $route;
        } else {
            // relative to module
            return ltrim(Yii::$app->controller->module->getUniqueId() . '/' . $route, '/');
        }
    }

    /**
     * Creates a URL based on the given parameters.
     *
     * This method is very similar to [[toRoute()]]. The only difference is that this method
     * requires a route to be specified as an array only. If a string is given, it will be treated as a URL.
     * In particular, if `$url` is
     *
     * - an array: [[toRoute()]] will be called to generate the URL. For example:
     *   `['site/index']`, `['post/index', 'page' => 2]`. Please refer to [[toRoute()]] for more details
     *   on how to specify a route.
     * - a string with a leading `@`: it is treated as an alias, and the corresponding aliased string
     *   will be returned.
     * - an empty string: the currently requested URL will be returned;
     * - a normal string: it will be returned as is.
     *
     * When `$scheme` is specified (either a string or true), an absolute URL with host info (obtained from
     * [[\yii\web\UrlManager::hostInfo]]) will be returned. If `$url` is already an absolute URL, its scheme
     * will be replaced with the specified one.
     *
     * Below are some examples of using this method:
     *
     * ```php
     * // /index.php?r=site%2Findex
     * echo Url::to(['site/index']);
     *
     * // /index.php?r=site%2Findex&src=ref1#name
     * echo Url::to(['site/index', 'src' => 'ref1', '#' => 'name']);
     *
     * // /index.php?r=post%2Findex     assume the alias "@posts" is defined as "/post/index"
     * echo Url::to(['@posts']);
     *
     * // the currently requested URL
     * echo Url::to();
     *
     * // /images/logo.gif
     * echo Url::to('@web/images/logo.gif');
     *
     * // images/logo.gif
     * echo Url::to('images/logo.gif');
     *
     * // http://www.example.com/images/logo.gif
     * echo Url::to('@web/images/logo.gif', true);
     *
     * // https://www.example.com/images/logo.gif
     * echo Url::to('@web/images/logo.gif', 'https');
     * ```
     *
     *
     * @param array|string $url the parameter to be used to generate a valid URL
     * @param boolean|string $scheme the URI scheme to use in the generated URL:
     *
     * - `false` (default): generating a relative URL.
     * - `true`: returning an absolute base URL whose scheme is the same as that in [[\yii\web\UrlManager::hostInfo]].
     * - string: generating an absolute URL with the specified scheme (either `http` or `https`).
     *
     * @return string the generated URL
     * @throws InvalidParamException a relative route is given while there is no active controller
     */
    public static function to($url = '', $scheme = false)
    {
        if (is_array($url)) {
            return static::toRoute($url, $scheme);
        }

        $url = Yii::getAlias($url);
        if ($url === '') {
            $url = Yii::$app->getRequest()->getUrl();
        }

        if (!$scheme) {
            return $url;
        }

        if (strncmp($url, '//', 2) === 0) {
            // e.g. //hostname/path/to/resource
            return is_string($scheme) ? "$scheme:$url" : $url;
        }

        if (($pos = strpos($url, ':')) === false || !ctype_alpha(substr($url, 0, $pos))) {
            // turn relative URL into absolute
            $url = static::getUrlManager()->getHostInfo() . '/' . ltrim($url, '/');
        }

        if (is_string($scheme) && ($pos = strpos($url, ':')) !== false) {
            // replace the scheme with the specified one
            $url = $scheme . substr($url, $pos);
        }

        return $url;
    }

    /**
     * Returns the base URL of the current request.
     * @param boolean|string $scheme the URI scheme to use in the returned base URL:
     *
     * - `false` (default): returning the base URL without host info.
     * - `true`: returning an absolute base URL whose scheme is the same as that in [[\yii\web\UrlManager::hostInfo]].
     * - string: returning an absolute base URL with the specified scheme (either `http` or `https`).
     * @return string
     */
    public static function base($scheme = false)
    {
        $url = static::getUrlManager()->getBaseUrl();
        if ($scheme) {
            $url = static::getUrlManager()->getHostInfo() . $url;
            if (is_string($scheme) && ($pos = strpos($url, '://')) !== false) {
                $url = $scheme . substr($url, $pos);
            }
        }
        return $url;
    }

    /**
     * Remembers the specified URL so that it can be later fetched back by [[previous()]].
     *
     * @param string|array $url the URL to remember. Please refer to [[to()]] for acceptable formats.
     * If this parameter is not specified, the currently requested URL will be used.
     * @param string $name the name associated with the URL to be remembered. This can be used
     * later by [[previous()]]. If not set, it will use [[\yii\web\User::returnUrlParam]].
     * @see previous()
     */
    public static function remember($url = '', $name = null)
    {
        $url = static::to($url);

        if ($name === null) {
            Yii::$app->getUser()->setReturnUrl($url);
        } else {
            Yii::$app->getSession()->set($name, $url);
        }
    }

    /**
     * Returns the URL previously [[remember()|remembered]].
     *
     * @param string $name the named associated with the URL that was remembered previously.
     * If not set, it will use [[\yii\web\User::returnUrlParam]].
     * @return string the URL previously remembered. Null is returned if no URL was remembered with the given name.
     * @see remember()
     */
    public static function previous($name = null)
    {
        if ($name === null) {
            return Yii::$app->getUser()->getReturnUrl();
        } else {
            return Yii::$app->getSession()->get($name);
        }
    }

    /**
     * Returns the canonical URL of the currently requested page.
     * The canonical URL is constructed using the current controller's [[\yii\web\Controller::route]] and
     * [[\yii\web\Controller::actionParams]]. You may use the following code in the layout view to add a link tag
     * about canonical URL:
     *
     * ```php
     * $this->registerLinkTag(['rel' => 'canonical', 'href' => Url::canonical()]);
     * ```
     *
     * @return string the canonical URL of the currently requested page
     */
    public static function canonical()
    {
        $params = Yii::$app->controller->actionParams;
        $params[0] = Yii::$app->controller->getRoute();

        return static::getUrlManager()->createAbsoluteUrl($params);
    }

    /**
     * Returns the home URL.
     *
     * @param boolean|string $scheme the URI scheme to use for the returned URL:
     *
     * - `false` (default): returning a relative URL.
     * - `true`: returning an absolute base URL whose scheme is the same as that in [[\yii\web\UrlManager::hostInfo]].
     * - string: returning an absolute URL with the specified scheme (either `http` or `https`).
     *
     * @return string home URL
     */
    public static function home($scheme = false)
    {
        $url = Yii::$app->getHomeUrl();

        if ($scheme) {
            $url = static::getUrlManager()->getHostInfo() . $url;
            if (is_string($scheme) && ($pos = strpos($url, '://')) !== false) {
                $url = $scheme . substr($url, $pos);
            }
        }

        return $url;
    }

    /**
     * Returns a value indicating whether a URL is relative.
     * A relative URL does not have host info part.
     * @param string $url the URL to be checked
     * @return boolean whether the URL is relative
     */
    public static function isRelative($url)
    {
        return strncmp($url, '//', 2) && strpos($url, '://') === false;
    }

    /**
     * Creates a URL by using the current route and the GET parameters.
     *
     * You may modify or remove some of the GET parameters, or add additional query parameters through
     * the `$params` parameter. In particular, if you specify a parameter to be null, then this parameter
     * will be removed from the existing GET parameters; all other parameters specified in `$params` will
     * be merged with the existing GET parameters. For example,
     *
     * ```php
     * // assume $_GET = ['id' => 123, 'src' => 'google'], current route is "post/view"
     *
     * // /index.php?r=post%2Fview&id=123&src=google
     * echo Url::current();
     *
     * // /index.php?r=post%2Fview&id=123
     * echo Url::current(['src' => null]);
     *
     * // /index.php?r=post%2Fview&id=100&src=google
     * echo Url::current(['id' => 100]);
     * ```
     *
     * @param array $params an associative array of parameters that will be merged with the current GET parameters.
     * If a parameter value is null, the corresponding GET parameter will be removed.
     * @param boolean|string $scheme the URI scheme to use in the generated URL:
     *
     * - `false` (default): generating a relative URL.
     * - `true`: returning an absolute base URL whose scheme is the same as that in [[\yii\web\UrlManager::hostInfo]].
     * - string: generating an absolute URL with the specified scheme (either `http` or `https`).
     *
     * @return string the generated URL
     * @since 2.0.3
     */
    public static function current(array $params = [], $scheme = false)
    {
        $currentParams = Yii::$app->getRequest()->getQueryParams();
        $currentParams[0] = '/' . Yii::$app->controller->getRoute();
        $route = ArrayHelper::merge($currentParams, $params);
        return static::toRoute($route, $scheme);
    }

    /**
     * @return \yii\web\UrlManager URL manager used to create URLs
     * @since 2.0.8
     */
    protected static function getUrlManager()
    {
        return static::$urlManager ?: Yii::$app->getUrlManager();
    }
}
