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
 * UrlNormalizer normalizes URLs for [[UrlManager]] and [[UrlRule]].
 *
 * @author Robert Korulczyk <robert@korulczyk.pl>
 * @author Cronfy <cronfy@gmail.com>
 * @since 2.0.10
 */
class UrlNormalizer extends Object
{
    /**
     * Represents permament redirection during route normalization.
     * @see https://en.wikipedia.org/wiki/HTTP_301
     */
    const ACTION_REDIRECT_PERMANENT = 301;
    /**
     * Represents temporary redirection during route normalization.
     * @see https://en.wikipedia.org/wiki/HTTP_302
     */
    const ACTION_REDIRECT_TEMPORARY = 302;
    /**
     * Represents showing 404 error page during route normalization.
     * @see https://en.wikipedia.org/wiki/HTTP_404
     */
    const ACTION_NOT_FOUND = 404;

    /**
     * @var boolean whether slashes should be collapsed, for example `site///index` will be
     * converted into `site/index`
     */
    public $collapseSlashes = true;
    /**
     * @var boolean whether trailing slash should be normalized according to the suffix settings
     * of the rule
     */
    public $normalizeTrailingSlash = true;
    /**
     * @var integer|callable|null action to perform during route normalization.
     * Available options are:
     * - `null` - no special action will be performed
     * - `301` - the request should be redirected to the normalized URL using
     *   permanent redirection
     * - `302` - the request should be redirected to the normalized URL using
     *   temporary redirection
     * - `404` - [[NotFoundHttpException]] will be thrown
     * - `callable` - custom user callback, for example:
     *
     *   ```php
     *   function ($route, $normalizer) {
     *       // use custom action for redirections
     *       $route[1]['oldRoute'] = $route[0];
     *       $route[0] = 'site/redirect';
     *       return $route;
     *   }
     *   ```
     */
    public $action = self::ACTION_REDIRECT_PERMANENT;


    /**
     * Performs normalization action for the specified $route.
     * @param array $route route for normalization
     * @return array normalized route
     * @throws InvalidConfigException if invalid normalization action is used.
     * @throws UrlNormalizerRedirectException if normalization requires redirection.
     * @throws NotFoundHttpException if normalization suggests action matching route does not exist.
     */
    public function normalizeRoute($route)
    {
        if ($this->action === null) {
            return $route;
        } elseif ($this->action === static::ACTION_REDIRECT_PERMANENT || $this->action === static::ACTION_REDIRECT_TEMPORARY) {
            throw new UrlNormalizerRedirectException([$route[0]] + $route[1], $this->action);
        } elseif ($this->action === static::ACTION_NOT_FOUND) {
            throw new NotFoundHttpException(Yii::t('yii', 'Page not found.'));
        } elseif (is_callable($this->action)) {
            return call_user_func($this->action, $route, $this);
        }

        throw new InvalidConfigException('Invalid normalizer action.');
    }

    /**
     * Normalizes specified pathInfo.
     * @param string $pathInfo pathInfo for normalization
     * @param string $suffix current rule suffix
     * @param boolean $normalized if specified, this variable will be set to `true` if $pathInfo
     * was changed during normalization
     * @return string normalized pathInfo
     */
    public function normalizePathInfo($pathInfo, $suffix, &$normalized = false)
    {
        if (empty($pathInfo)) {
            return $pathInfo;
        }

        $sourcePathInfo = $pathInfo;
        if ($this->collapseSlashes) {
            $pathInfo = $this->collapseSlashes($pathInfo);
        }

        if ($this->normalizeTrailingSlash === true) {
            $pathInfo = $this->normalizeTrailingSlash($pathInfo, $suffix);
        }

        $normalized = $sourcePathInfo !== $pathInfo;

        return $pathInfo;
    }

    /**
     * Collapse consecutive slashes in $pathInfo, for example converts `site///index` into `site/index`.
     * @param string $pathInfo raw path info.
     * @return string normalized path info.
     */
    protected function collapseSlashes($pathInfo)
    {
        return ltrim(preg_replace('#/{2,}#', '/', $pathInfo), '/');
    }

    /**
     * Adds or removes trailing slashes from $pathInfo depending on whether the $suffix has a
     * trailing slash or not.
     * @param string $pathInfo raw path info.
     * @param string $suffix
     * @return string normalized path info.
     */
    protected function normalizeTrailingSlash($pathInfo, $suffix)
    {
        if (substr($suffix, -1) === '/' && substr($pathInfo, -1) !== '/') {
            $pathInfo .= '/';
        } elseif (substr($suffix, -1) !== '/' && substr($pathInfo, -1) === '/') {
            $pathInfo = rtrim($pathInfo, '/');
        }

        return $pathInfo;
    }
}