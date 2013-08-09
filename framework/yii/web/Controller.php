<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\InlineAction;

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
	 * Binds the parameters to the action.
	 * This method is invoked by [[Action]] when it begins to run with the given parameters.
	 * This method will check the parameter names that the action requires and return
	 * the provided parameters according to the requirement. If there is any missing parameter,
	 * an exception will be thrown.
	 * @param \yii\base\Action $action the action to be bound with parameters
	 * @param array $params the parameters to be bound to the action
	 * @return array the valid parameters that the action can run with.
	 * @throws HttpException if there are missing parameters.
	 */
	public function bindActionParams($action, $params)
	{
		if ($action instanceof InlineAction) {
			$method = new \ReflectionMethod($this, $action->actionMethod);
		} else {
			$method = new \ReflectionMethod($action, 'run');
		}

		$args = array();
		$missing = array();
		foreach ($method->getParameters() as $param) {
			$name = $param->getName();
			if (array_key_exists($name, $params)) {
				$args[] = $params[$name];
				unset($params[$name]);
			} elseif ($param->isDefaultValueAvailable()) {
				$args[] = $param->getDefaultValue();
			} else {
				$missing[] = $name;
			}
		}

		if (!empty($missing)) {
			throw new HttpException(400, Yii::t('yii', 'Missing required parameters: {params}', array(
				'{params}' => implode(', ', $missing),
			)));
		}

		return $args;
	}

	/**
	 * Creates a URL using the given route and parameters.
	 *
	 * This method enhances [[UrlManager::createUrl()]] by supporting relative routes.
	 * A relative route is a route without a leading slash, such as "view", "post/view".
	 *
	 * - If the route is an empty string, the current [[route]] will be used;
	 * - If the route contains no slashes at all, it is considered to be an action ID
	 *   of the current controller and will be prepended with [[uniqueId]];
	 * - If the route has no leading slash, it is considered to be a route relative
	 *   to the current module and will be prepended with the module's uniqueId.
	 *
	 * After this route conversion, the method calls [[UrlManager::createUrl()]] to create a URL.
	 *
	 * @param string $route the route. This can be either an absolute route or a relative route.
	 * @param array $params the parameters (name-value pairs) to be included in the generated URL
	 * @return string the created URL
	 */
	public function createUrl($route, $params = array())
	{
		if (strpos($route, '/') === false) {
			// empty or an action ID
			$route = $route === '' ? $this->getRoute() : $this->getUniqueId() . '/' . $route;
		} elseif ($route[0] !== '/') {
			// relative to module
			$route = ltrim($this->module->getUniqueId() . '/' . $route, '/');
		}
		return Yii::$app->getUrlManager()->createUrl($route, $params);
	}

	/**
	 * Redirects the browser to the specified URL.
	 * This method is a shortcut to [[Response::redirect()]].
	 *
	 * @param array|string $url the URL to be redirected to. [[\yii\helpers\Html::url()]]
	 * will be used to normalize the URL. If the resulting URL is still a relative URL
	 * (one without host info), the current request host info will be used.
	 * @param integer $statusCode the HTTP status code. If null, it will use 302
	 * for normal requests, and [[ajaxRedirectCode]] for AJAX requests.
	 * See [[http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html]]
	 * for details about HTTP status code
	 * @return Response the response object itself
	 */
	public function redirect($url, $statusCode = null)
	{
		return Yii::$app->getResponse()->redirect($url, $statusCode);
	}

	/**
	 * Refreshes the current page.
	 * This method is a shortcut to [[Response::refresh()]].
	 * @param string $anchor the anchor that should be appended to the redirection URL.
	 * Defaults to empty. Make sure the anchor starts with '#' if you want to specify it.
	 * @return Response the response object itself
	 */
	public function refresh($anchor = '')
	{
		return Yii::$app->getResponse()->redirect(Yii::$app->getRequest()->getUrl() . $anchor);
	}
}
