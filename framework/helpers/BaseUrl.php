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
	 * Returns URL for a route.
	 *
	 * @param array|string $route route as a string or route and parameters in form of
	 * ['route', 'param1' => 'value1', 'param2' => 'value2'].
	 *
	 * In case there is no controller, [[\yii\web\UrlManager::createUrl()]] will be used.
	 *
	 * @param string $schema URI schema to use. If specified absolute URL with the schema specified is returned.
	 * @return string the normalized URL
	 * @throws InvalidParamException if the parameter is invalid.
	 */
	public static function toRoute($route, $schema = null)
	{
		$route = (array)$route;
		if (!isset($route[0])) {
			throw new InvalidParamException('$route should contain at least one element.');
		}
		if (Yii::$app->controller instanceof \yii\web\Controller) {
			return $schema === null ? Yii::$app->controller->createUrl($route) : Yii::$app->controller->createAbsoluteUrl($route, $schema);
		} else {
			return $schema === null ? Yii::$app->getUrlManager()->createUrl($route) : Yii::$app->getUrlManager()->createAbsoluteUrl($route, $schema);
		}
	}

	/**
	 * Creates a link specified by the input parameter.
	 *
	 * If the input parameter
	 *
	 * - is an array: the first array element is considered a route, while the rest of the name-value
	 *   pairs are treated as the parameters to be used for URL creation using [[\yii\web\Controller::createUrl()]].
	 *   For example: `['post/index', 'page' => 2]`, `['index']`.
	 *   In case there is no controller, [[\yii\web\UrlManager::createUrl()]] will be used.
	 * - is an empty string: the currently requested URL will be returned;
	 * - is a non-empty string: it will first be processed by [[Yii::getAlias()]]. If the result
	 *   is an absolute URL, it will be returned without any change further; Otherwise, the result
	 *   will be prefixed with [[\yii\web\Request::baseUrl]] and returned.

	 *
	 * @param array|string $url the parameter to be used to generate a valid URL
	 * @param string $schema URI schema to use. If specified absolute URL with the schema specified is returned.
	 * @return string the normalized URL
	 * @throws InvalidParamException if the parameter is invalid.
	 */
	public static function to($url = '', $schema = null)
	{
		if (is_array($url)) {
			return static::toRoute($url, $schema);
		} elseif ($url === '') {
			if ($schema === null) {
				return Yii::$app->request->getUrl();
			} else {
				$url = Yii::$app->request->getAbsoluteUrl();
				$pos = strpos($url, '://');
				return $schema . substr($url, $pos);
			}
		} else {
			$url = Yii::getAlias($url);
			$pos = strpos($url, '://');
			if ($pos !== null) {
				if ($schema !== null) {
					$url = $schema . substr($url, $pos);
				}
				return $url;
			}

			if ($url !== '' && ($url[0] === '/' || $url[0] === '#' || !strncmp($url, './', 2))) {
				$url = Yii::$app->getRequest()->getHostInfo() . $url;
			} else {
				$url = Yii::$app->getRequest()->getBaseUrl() . '/' . $url;
			}
			if ($schema !== null) {
				$pos = strpos($url, '://');
				if ($pos !== null) {
					$url = $schema . substr($url, $pos);
				}
			}
			return $url;
		}
	}

	/**
	 * Remembers URL passed
	 *
	 * @param string $url URL to remember. Default is current URL.
	 * @param string $name Name to use to remember URL. Defaults to `yii\web\User::returnUrlParam`.
	 */
	public function remember($url = '', $name = null)
	{
		if ($url === '') {
			$url = Yii::$app->getRequest()->getUrl();
		}

		if ($name === null) {
			Yii::$app->getUser()->setReturnUrl($url);
		} else {
			Yii::$app->getSession()->set($name, $url);
		}
	}

	/**
	 * Returns URL previously saved with remember method
	 *
	 * @param string $name Name used to remember URL. Defaults to `yii\web\User::returnUrlParam`.
	 * @return string URL
	 */
	public function previous($name = null)
	{
		if ($name === null) {
			return Yii::$app->getUser()->getReturnUrl();
		} else {
			return Yii::$app->getSession()->get($name);
		}
	}

	/**
	 * Returns canonical URL for the current page
	 *
	 * @return string canonical URL
	 */
	public function canonical()
	{
		return Yii::$app->controller->getCanonicalUrl();
	}

	/**
	 * Returns home URL
	 *
	 * @param string $schema URI schema to use. If specified absolute URL with the schema specified is returned.
	 * @return string home URL
	 */
	public function home($schema = null)
	{
		if ($schema === null) {
			return Yii::$app->getHomeUrl();
		} else {
			$url = Yii::$app->getRequest()->getHostInfo() . Yii::$app->getHomeUrl();
			$pos = strpos($url, '://');
			return $schema . substr($url, $pos);
		}
	}
}
 