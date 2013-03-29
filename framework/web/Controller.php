<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\helpers\Html;

/**
 * Controller is the base class of Web controllers.
 *
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Controller extends \yii\base\Controller
{
	/**
	 * Creates a URL using the given route and parameters.
	 *
	 * This method enhances [[UrlManager::createUrl()]] by supporting relative routes.
	 * A relative route is a route without a slash, such as "view". If the route is an empty
	 * string, [[route]] will be used; Otherwise, [[uniqueId]] will be prepended to a relative route.
	 *
	 * After this route conversion, the method This method calls [[UrlManager::createUrl()]]
	 * to create a URL.
	 *
	 * @param string $route the route. This can be either an absolute route or a relative route.
	 * @param array $params the parameters (name-value pairs) to be included in the generated URL
	 * @return string the created URL
	 */
	public function createUrl($route, $params = array())
	{
		if (strpos($route, '/') === false) {
			// a relative route
			$route = $route === '' ? $this->getRoute() : $this->getUniqueId() . '/' . $route;
		}
		return Yii::$app->getUrlManager()->createUrl($route, $params);
	}

	/**
	 * Redirects the browser to the specified URL or route (controller/action).
	 * @param mixed $url the URL to be redirected to. If the parameter is an array,
	 * the first element must be a route to a controller action and the rest
	 * are GET parameters in name-value pairs.
	 * @param boolean $terminate whether to terminate the current application after calling this method. Defaults to true.
	 * @param integer $statusCode the HTTP status code. Defaults to 302. See {@link http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html}
	 * for details about HTTP status code.
	 */
	public function redirect($url, $terminate = true, $statusCode = 302)
	{
		$url = Html::url($url);
		Yii::$app->getResponse()->redirect($url, $terminate, $statusCode);
	}
}