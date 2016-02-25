<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\filters;

use Yii;
use yii\base\ActionFilter;
use yii\base\Action;

/**
 * HttpCache implements client-side caching by utilizing the `Last-Modified` and `Etag` HTTP headers.
 *
 * It is an action filter that can be added to a controller and handles the `beforeAction` event.
 *
 * To use HttpCache, declare it in the `behaviors()` method of your controller class.
 * In the following example the filter will be applied to the `list`-action and
 * the Last-Modified header will contain the date of the last update to the user table in the database.
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
 * //                return // generate etag seed here
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
     */
    public $lastModified;
    /**
     * @var callable a PHP callback that generates the Etag seed string.
     * The callback's signature should be:
     *
     * ```php
     * function ($action, $params)
     * ```
     *
     * where `$action` is the [[Action]] object that this filter is currently handling;
     * `$params` takes the value of [[params]]. The callback should return a string serving
     * as the seed for generating an Etag.
     */
    public $etagSeed;
    /**
     * @var mixed additional parameters that should be passed to the [[lastModified]] and [[etagSeed]] callbacks.
     */
    public $params;
    /**
     * @var string the value of the `Cache-Control` HTTP header. If null, the header will not be sent.
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
     * @var boolean a value indicating whether this filter should be enabled.
     */
    public $enabled = true;


    /**
     * This method is invoked right before an action is to be executed (after all possible filters.)
     * You may override this method to do last-minute preparation for the action.
     * @param Action $action the action to be executed.
     * @return boolean whether the action should continue to be executed.
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
            $etag = $this->generateEtag($seed);
        }

        $this->sendCacheControlHeader();

        $response = Yii::$app->getResponse();
        if ($etag !== null) {
            $response->getHeaders()->set('Etag', $etag);
        }

        if ($this->validateCache($lastModified, $etag)) {
            $response->setStatusCode(304);
            return false;
        }

        if ($lastModified !== null) {
            $response->getHeaders()->set('Last-Modified', gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
        }

        return true;
    }

    /**
     * Validates if the HTTP cache contains valid content.
     * @param integer $lastModified the calculated Last-Modified value in terms of a UNIX timestamp.
     * If null, the Last-Modified header will not be validated.
     * @param string $etag the calculated ETag value. If null, the ETag header will not be validated.
     * @return boolean whether the HTTP cache is still valid.
     */
    protected function validateCache($lastModified, $etag)
    {
        if (isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
            // HTTP_IF_NONE_MATCH takes precedence over HTTP_IF_MODIFIED_SINCE
            // http://tools.ietf.org/html/rfc7232#section-3.3
            return $etag !== null && in_array($etag, Yii::$app->request->getETags(), true);
        } elseif (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            return $lastModified !== null && @strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $lastModified;
        } else {
            return $etag === null && $lastModified === null;
        }
    }

    /**
     * Sends the cache control header to the client
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
            session_cache_limiter($this->sessionCacheLimiter);
        }

        $headers = Yii::$app->getResponse()->getHeaders();
        $headers->set('Pragma');

        if ($this->cacheControlHeader !== null) {
            $headers->set('Cache-Control', $this->cacheControlHeader);
        }
    }

    /**
     * Generates an Etag from the given seed string.
     * @param string $seed Seed for the ETag
     * @return string the generated Etag
     */
    protected function generateEtag($seed)
    {
        return '"' . rtrim(base64_encode(sha1($seed, true)), '=') . '"';
    }
}
