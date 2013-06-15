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
