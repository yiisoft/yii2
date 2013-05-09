<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;

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
}
