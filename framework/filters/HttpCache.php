<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\filters;

use Yii;
use yii\base\Action;
use yii\base\ActionFilter;

/**
 * HttpCache 通过利用 `Last-Modified` 和 `ETag` HTTP headers 来实现客户端缓存。
 *
 * 它是一个动作过滤器可以添加到控制器中并处理 `beforeAction` 事件。
 *
 * 要使用 HttpCache，请在控制器类的 `behaviors()` 方法中声明它。
 * 在下面的示例中过滤器将应用于 `index` 操作
 * 最后修改的标题将包含数据库中用户表的最后更新日期。
 *
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         [
 *             'class' => 'yii\filters\HttpCache',
 *             'only' => ['index'],
 *             'lastModified' => function ($action, $params) {
 *                 $q = new \yii\db\Query();
 *                 return $q->from('user')->max('updated_at');
 *             },
 * //            'etagSeed' => function ($action, $params) {
 * //                return // generate ETag seed here
 * //            }
 *         ],
 *     ];
 * }
 * ```
 *
 * @author Da:Sourcerer <webmaster@dasourcerer.net>
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HttpCache extends ActionFilter
{
    /**
     * @var callable 一个 php 回调返回上次修改时间的 UNIX 时间戳。
     * 回调的签名应为：
     *
     * ```php
     * function ($action, $params)
     * ```
     *
     * 其中`$action` 是此筛选器当前正在处理的 [[Action]] 对象；
     * `$params` 接受 [[params]] 的值。回调应该返回UNIX时间戳。
     *
     * @see http://tools.ietf.org/html/rfc7232#section-2.2
     */
    public $lastModified;
    /**
     * @var callable 生成 ETag 种子字符串的 PHP 回调。
     * 回调的签名应为：
     *
     * ```php
     * function ($action, $params)
     * ```
     *
     * 其中`$action` 是此筛选器当前正在处理的 [[Action]] 对象；
     * `$params` 接受 [[params]] 的值。 回调应该返回
     * 一个字符串作为生成 ETag 的种子。
     */
    public $etagSeed;
    /**
     * @var bool 是否生成弱 ETags。
     *
     * 如果内容在语义上是等价的而不是字节相等，则应使用弱 ETags 。
     *
     * @since 2.0.8
     * @see http://tools.ietf.org/html/rfc7232#section-2.3
     */
    public $weakEtag = false;
    /**
     * @var mixed 应传递给 [[lastModified]] 和 [[etagSeed]] 回调的其他参数。
     */
    public $params;
    /**
     * @var string `Cache-Control` HTTP header的值，如果为 null, 则不会发送。
     * @see http://tools.ietf.org/html/rfc2616#section-14.9
     */
    public $cacheControlHeader = 'public, max-age=3600';
    /**
     * @var string 调用 [session_cache_limiter()](http://www.php.net/manual/en/function.session-cache-limiter.php)
     * 时要设置的缓存限制器的名称。 默认值为空字符串，这意味着完全关闭缓存标头的自动发送。
     * 您可以将此属性设置为 `public`, `private`, `private_no_expire`，和 `nocache`。
     * 请参阅 [session_cache_limiter()](http://www.php.net/manual/en/function.session-cache-limiter.php)
     * 有关这些值的详细说明.
     *
     * 如果此属性为 `null`，则不会调用`session_cache_limiter()` 。结果，
     * PHP 将根据‘session.cache_limiter` PHP ini 设置发送headers 。
     */
    public $sessionCacheLimiter = '';
    /**
     * @var bool 指示是否应启用此筛选器的值。
     */
    public $enabled = true;


    /**
     * 此方法是在执行操作之前（在所有可能的筛选器之后）调用的。
     * 您可以重写此方法来为操作做最后一刻的准备。
     * @param Action $action 要执行的操作。
     * @return bool 是否应继续执行该操作。
     */
    public function beforeAction($action)
    {
        if (!$this->enabled) {
            return true;
        }

        $verb = Yii::$app->getRequest()->getMethod();
        if ($verb !== 'GET' && $verb !== 'HEAD' || $this->lastModified === null && $this->etagSeed === null) {
            return true;
        }

        $lastModified = $etag = null;
        if ($this->lastModified !== null) {
            $lastModified = call_user_func($this->lastModified, $action, $this->params);
        }
        if ($this->etagSeed !== null) {
            $seed = call_user_func($this->etagSeed, $action, $this->params);
            if ($seed !== null) {
                $etag = $this->generateEtag($seed);
            }
        }

        $this->sendCacheControlHeader();

        $response = Yii::$app->getResponse();
        if ($etag !== null) {
            $response->getHeaders()->set('Etag', $etag);
        }

        $cacheValid = $this->validateCache($lastModified, $etag);
        // https://tools.ietf.org/html/rfc7232#section-4.1
        if ($lastModified !== null && (!$cacheValid || ($cacheValid && $etag === null))) {
            $response->getHeaders()->set('Last-Modified', gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
        }
        if ($cacheValid) {
            $response->setStatusCode(304);
            return false;
        }

        return true;
    }

    /**
     * 验证 HTTP 缓存是否包含有效内容。
     * 如果 Last-Modified 和 ETag 均为 null，则返回False。
     * @param int $lastModified 根据 UNIX 时间戳计算 Last-Modified 值。
     * 如果为 null，则不会验证 Last-Modified header。
     * @param string $etag 计算的 ETag 值。如果为 null，则不会验证 ETag header。
     * @return bool HTTP 缓存是否仍然有效。
     */
    protected function validateCache($lastModified, $etag)
    {
        if (Yii::$app->request->headers->has('If-None-Match')) {
            // HTTP_IF_NONE_MATCH takes precedence over HTTP_IF_MODIFIED_SINCE
            // http://tools.ietf.org/html/rfc7232#section-3.3
            return $etag !== null && in_array($etag, Yii::$app->request->getETags(), true);
        } elseif (Yii::$app->request->headers->has('If-Modified-Since')) {
            return $lastModified !== null && @strtotime(Yii::$app->request->headers->get('If-Modified-Since')) >= $lastModified;
        }

        return false;
    }

    /**
     * 将缓存控制标头发送到客户端。
     * @see cacheControlHeader
     */
    protected function sendCacheControlHeader()
    {
        if ($this->sessionCacheLimiter !== null) {
            if ($this->sessionCacheLimiter === '' && !headers_sent() && Yii::$app->getSession()->getIsActive()) {
                header_remove('Expires');
                header_remove('Cache-Control');
                header_remove('Last-Modified');
                header_remove('Pragma');
            }

            Yii::$app->getSession()->setCacheLimiter($this->sessionCacheLimiter);
        }

        $headers = Yii::$app->getResponse()->getHeaders();

        if ($this->cacheControlHeader !== null) {
            $headers->set('Cache-Control', $this->cacheControlHeader);
        }
    }

    /**
     * 从给定的种子字符串生成 ETag。
     * @param string $seed Seed for the ETag
     * @return string the generated ETag
     */
    protected function generateEtag($seed)
    {
        $etag = '"' . rtrim(base64_encode(sha1($seed, true)), '=') . '"';
        return $this->weakEtag ? 'W/' . $etag : $etag;
    }
}
