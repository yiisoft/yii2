<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\helpers;

use yii\base\InvalidParamException;
use Yii;

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
	 * Normalizes the input parameter to be a valid URL.
	 *
	 * If the input parameter
	 *
	 * - is an empty string: the currently requested URL will be returned;
	 * - is a non-empty string: it will first be processed by [[Yii::getAlias()]]. If the result
	 *   is an absolute URL, it will be returned without any change further; Otherwise, the result
	 *   will be prefixed with [[\yii\web\Request::baseUrl]] and returned.
	 * - is an array: the first array element is considered a route, while the rest of the name-value
	 *   pairs are treated as the parameters to be used for URL creation using [[\yii\web\Controller::createUrl()]].
	 *   For example: `['post/index', 'page' => 2]`, `['index']`.
	 *   In case there is no controller, [[\yii\web\UrlManager::createUrl()]] will be used.
	 *
	 * @param array|string $url the parameter to be used to generate a valid URL
	 * @return string the normalized URL
	 * @throws InvalidParamException if the parameter is invalid.
	 */
	public static function create($url)
	{
		if (is_array($url)) {
			if (isset($url[0])) {
				if (Yii::$app->controller instanceof \yii\web\Controller) {
					return Yii::$app->controller->createUrl($url);
				} else {
					return Yii::$app->getUrlManager()->createUrl($url);
				}
			} else {
				throw new InvalidParamException('The array specifying a URL must contain at least one element.');
			}
		} elseif ($url === '') {
			return Yii::$app->getRequest()->getUrl();
		} else {
			$url = Yii::getAlias($url);
			if ($url !== '' && ($url[0] === '/' || $url[0] === '#' || strpos($url, '://') || !strncmp($url, './', 2))) {
				return $url;
			} else {
				return static::asset($url);
			}
		}
	}

	/**
	 * Prefixes relative URL with base URL
	 *
	 * @param string $url relative URL
	 * @return string absolute URL
	 */
	public function asset($url)
	{
		return Yii::$app->getRequest()->getBaseUrl() . '/' . $url;
	}

	/**
	 * Sets current URL as return URL
	 */
	public function rememberReturnUrl()
	{
		Yii::$app->user->setReturnUrl(Yii::$app->getRequest()->getUrl());
	}
}
 