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
	 * @param array|string $route parametrized route or empty string for current URL:
	 *
	 * - an empty string: the currently requested URL will be returned;
	 * - an array: the first array element is considered a route, while the rest of the name-value
	 *   pairs are treated as the parameters to be used for URL creation using [[\yii\web\Controller::createUrl()]].
	 *   For example: `['post/index', 'page' => 2]`, `['index']`.
	 *   In case there is no controller, [[\yii\web\UrlManager::createUrl()]] will be used.
	 *
	 * @param boolean $absolute if absolute URL should be created
	 * @return string the normalized URL
	 * @throws InvalidParamException if the parameter is invalid.
	 */
	public static function toRoute($route, $absolute = false)
	{
		if ($route === '') {
			return $absolute ? Yii::$app->getRequest()->getAbsoluteUrl() : Yii::$app->getRequest()->getUrl();
		}
		if (!is_array($route) || !isset($route[0])) {
			throw new InvalidParamException('$route should be either empty string or array containing at least one element.');
		}
		if (Yii::$app->controller instanceof \yii\web\Controller) {
			return $absolute ? Yii::$app->controller->createAbsoluteUrl($route) : Yii::$app->controller->createUrl($route);
		} else {
			return $absolute ? Yii::$app->getUrlManager()->createAbsoluteUrl($route) : Yii::$app->getUrlManager()->createUrl($route);
		}
	}

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
	 * @param boolean $absolute if URL should be absolute
	 * @return string the normalized URL
	 * @throws InvalidParamException if the parameter is invalid.
	 */
	public static function to($url = null, $absolute = false)
	{
		if (is_array($url) && isset($url[0]) || $url === '') {
			return static::to($url, $absolute);
		} else {
			$url = Yii::getAlias($url);
			if ($url !== '' && ($url[0] === '/' || $url[0] === '#' || strpos($url, '://') || !strncmp($url, './', 2))) {
				return $url;
			} else {
				$prefix = $absolute ? Yii::$app->request->getHostInfo() : '';
				return $prefix . Yii::$app->getRequest()->getBaseUrl() . '/' . $url;
			}
		}
	}

	/**
	 * Remembers URL passed
	 *
	 * @param string $url URL to remember. Default is current URL.
	 * @param string $name Name to use to remember URL. Defaults to `yii\web\User::returnUrlParam`.
	 */
	public function remember($url = null, $name = null)
	{
		if ($url === null) {
			$url = Yii::$app->getRequest()->getUrl();
		}

		if ($name === null) {
			Yii::$app->user->setReturnUrl($url);
		} else {
			Yii::$app->session->set($name, $url);
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
			return Yii::$app->user->getReturnUrl();
		} else {
			return Yii::$app->session->get($name);
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
	 * @param boolean $absolute if absolute URL should be returned
	 * @return string home URL
	 */
	public function home($absolute = false)
	{
		$prefix = $absolute ? Yii::$app->request->getHostInfo() : '';
		return $prefix . Yii::$app->getHomeUrl();
	}
}
 