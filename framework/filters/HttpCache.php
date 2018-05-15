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
 * HttpCache implements client-side caching by utilizing the `Last-Modified` and `ETag` HTTP headers.
 *
 * It is an action filter that can be added to a controller and handles the `beforeAction` event.
 *
 * To use HttpCache, declare it in the `behaviors()` method of your controller class.
 * In the following example the filter will be applied to the `index` action and
 * the Last-Modified header will contain the date of the last update to the user table in the database.
 *
 * ```php
 * public function behaviors()
 * {
 *     return [
 *         [
 *             '__class' => \yii\filters\HttpCache::class,
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
     * @var callable a PHP callback that returns the UNIX timestamp of the last modification time.
     * The callback's signature should be:
     *
     * ```php
     * function ($action, $params)
     * ```
     *
     * where `$action` is the [[Action]] object that this filter is currently handling;
     * `$params` takes the value of [[params]]. The callback should return a UNIX timestamp.
     *
     * @see http://tools.ietf.org/html/rfc7232#section-2.2
     */
    public $lastModified;
    /**
     * @var callable a PHP callback that generates the ETag seed string.
     * The callback's signature should be:
     *
     * ```php
     * function ($action, $params)
     * ```
     *
     * where `$action` is the [[Action]] object that this filter is currently handling;
     * `$params` takes the value of [[params]]. The callback should return a string serving
     * as the seed for generating an ETag.
     */
    public $etagSeed;
    /**
     * @var bool whether to generate weak ETags.
     *
     * Weak ETags should be used if the content should be considered semantically equivalent, but not byte-equal.
     *
     * @since 2.0.8
     * @see http://tools.ietf.org/html/rfc7232#section-2.3
     */
    public $weakEtag = false;
    /**
     * @var mixed additional parameters that should be passed to the [[lastModified]] and [[etagSeed]] callbacks.
     */
    public $params;
    /**
     * @var string the value of the `Cache-Control` HTTP header. If null, the header will not be sent.
     * @see http://tools.ietf.org/html/rfc2616#section-14.9
     */
    public $cacheControlHeader = 'public, max-age=3600';
    /**
     * @var string the name of the cache limiter to be set when [session_cache_limiter()](http://www.php.net/manual/en/function.session-cache-limiter.php)
     * is called. The default value is an empty string, meaning turning off automatic sending of cache headers entirely.
     * You may set this property to be `public`, `private`, `private_no_expire`, and `nocache`.
     * Please refer to [session_cache_limiter()](http://www.php.net/manual/en/function.session-cache-limiter.php)
     * for detailed explanation of these values.
     *
     * If this property is `null`, then `session_cache_limiter()` will not be called. As a result,
     * PHP will send headers according to the `session.cache_limiter` PHP ini setting.
     */
    public $sessionCacheLimiter = '';
    /**
     * @var bool a value indicating whether this filter should be enabled.
     */
    public $enabled = true;


    /**
     * This method is invoked right before an action is to be executed (after all possible filters.)
     * You may override this method to do last-minute preparation for the action.
     * @param Action $action the action to be executed.
     * @return bool whether the action should continue to be executed.
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
            $response->setHeader('Etag', $etag);
        }

        $cacheValid = $this->validateCache($lastModified, $etag);
        // https://tools.ietf.org/html/rfc7232#section-4.1
        if ($lastModified !== null && (!$cacheValid || ($cacheValid && $etag === null))) {
            $response->setHeader('Last-Modified', gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
        }
        if ($cacheValid) {
            $response->setStatusCode(304);
            return false;
        }

        return true;
    }

    /**
     * Validates if the HTTP cache contains valid content.
     * If both Last-Modified and ETag are null, returns false.
     * @param int $lastModified the calculated Last-Modified value in terms of a UNIX timestamp.
     * If null, the Last-Modified header will not be validated.
     * @param string $etag the calculated ETag value. If null, the ETag header will not be validated.
     * @return bool whether the HTTP cache is still valid.
     */
    protected function validateCache($lastModified, $etag)
    {
        $request = Yii::$app->getRequest();
        if ($request->hasHeader('if-none-match')) {
            // 'if-none-match' takes precedence over 'if-modified-since'
            // http://tools.ietf.org/html/rfc7232#section-3.3
            return $etag !== null && in_array($etag, Yii::$app->request->getETags(), true);
        } elseif ($request->hasHeader('if-modified-since')) {
            return $lastModified !== null && @strtotime($request->getHeaderLine('if-modified-since')) >= $lastModified;
        }

        return false;
    }

    /**
     * Sends the cache control header to the client.
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

        if ($this->cacheControlHeader !== null) {
            Yii::$app->getResponse()->setHeader('Cache-Control', $this->cacheControlHeader);
        }
    }

    /**
     * Generates an ETag from the given seed string.
     * @param string $seed Seed for the ETag
     * @return string the generated ETag
     */
    protected function generateEtag($seed)
    {
        $etag = '"' . rtrim(base64_encode(sha1($seed, true)), '=') . '"';
        return $this->weakEtag ? 'W/' . $etag : $etag;
    }
}
