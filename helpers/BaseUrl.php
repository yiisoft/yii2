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
	 * @param array|string $params the parameter to be used to generate a valid URL
	 * @param boolean $absolute if absolute URL should be created
	 * @return string the normalized URL
	 * @throws InvalidParamException if the parameter is invalid.
	 */
	public static function create($params, $absolute = false)
	{
		if (is_array($params)) {
			if (isset($params[0])) {
				if (Yii::$app->controller instanceof \yii\web\Controller) {
					return $absolute ? Yii::$app->controller->createAbsoluteUrl($params) : Yii::$app->controller->createUrl($params);
				} else {
					return $absolute ? Yii::$app->getUrlManager()->createAbsoluteUrl($params) : Yii::$app->getUrlManager()->createUrl($params);
				}
			} else {
				throw new InvalidParamException('The array specifying a URL must contain at least one element.');
			}
		} elseif ($params === '') {
			return $absolute ? Yii::$app->getRequest()->getAbsoluteUrl() : Yii::$app->getRequest()->getUrl();
		} else {
			$params = Yii::getAlias($params);
			if ($params !== '' && ($params[0] === '/' || $params[0] === '#' || strpos($params, '://') || !strncmp($params, './', 2))) {
				return $params;
			} else {
				return static::base($params);
			}
		}
	}

	/**
	 * Normalizes the input parameter to be a valid absolute URL.
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
	 * @param array|string $params the parameter to be used to generate a valid URL
	 * @return string the normalized URL
	 * @throws InvalidParamException if the parameter is invalid.
	 */
	public static function createAbsolute($params)
	{
		return static::create($params, true);
	}

	/**
	 * Prefixes relative URL with base URL
	 *
	 * @param string $url relative URL
	 * @return string absolute URL
	 */
	public function base($url = null)
	{
		$result = Yii::$app->getRequest()->getBaseUrl();
		if ($url !== null) {
			$result .= '/' . $url;
		}
		return $result;
	}

	/**
	 * Sets current URL as return URL
	 */
	public function rememberReturn()
	{
		Yii::$app->user->setReturnUrl(Yii::$app->getRequest()->getUrl());
	}

	/**
	 * Returns canonical URL for the current page
	 *
	 * @return string canonical URL
	 */
	public function getCanonical()
	{
		return Yii::$app->controller->getCanonicalUrl();
	}

	/**
	 * Returns home URL
	 *
	 * @return string home URL
	 */
	public function getHome()
	{
		return Yii::$app->getHomeUrl();
	}
}
 