<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\ActionFilter;
use yii\base\Action;

/**
 * @author Da:Sourcerer <webmaster@dasourcerer.net>
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HttpCache extends ActionFilter
{
	/**
	 * @var callback a PHP callback that returns the UNIX timestamp of the last modification time.
	 * The callback's signature should be:
	 *
	 * ~~~
	 * function ($action, $params)
	 * ~~~
	 *
	 * where `$action` is the [[Action]] object that this filter is currently handling;
	 * `$params` takes the value of [[params]]. The callback should return a UNIX timestamp.
	 */
	public $lastModified;
	/**
	 * @var callback a PHP callback that generates the Etag seed string.
	 * The callback's signature should be:
	 *
	 * ~~~
	 * function ($action, $params)
	 * ~~~
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
	 * @var string HTTP cache control header. If null, the header will not be sent.
	 */
	public $cacheControlHeader = 'max-age=3600, public';

	/**
	 * This method is invoked right before an action is to be executed (after all possible filters.)
	 * You may override this method to do last-minute preparation for the action.
	 * @param Action $action the action to be executed.
	 * @return boolean whether the action should continue to be executed.
	 */
	public function beforeAction($action)
	{
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

		$response = $action->getResponse();
		$this->sendCacheControlHeader($response);
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
		if ($lastModified !== null && (!isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) || @strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) < $lastModified)) {
			return false;
		} else {
			return $etag === null || isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] === $etag;
		}
	}

	/**
	 * Sends the cache control header to the client
	 * @param Response $response
	 * @see cacheControl
	 */
	protected function sendCacheControlHeader($response)
	{
		session_cache_limiter('public');
		$headers = $response->getHeaders();
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
		return '"' . base64_encode(sha1($seed, true)) . '"';
	}
}
