<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\InlineAction;
use yii\helpers\Html;

/**
 * Controller is the base class of web controllers.
 *
 * @property string $canonicalUrl The canonical URL of the currently requested page. This property is
 * read-only.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Controller extends \yii\base\Controller
{
	/**
	 * @var boolean whether to enable CSRF validation for the actions in this controller.
	 * CSRF validation is enabled only when both this property and [[Request::enableCsrfValidation]] are true.
	 */
	public $enableCsrfValidation = true;
	/**
	 * @var array the parameters bound to the current action. This is mainly used by [[getCanonicalUrl()]].
	 */
	public $actionParams = [];

	/**
	 * Binds the parameters to the action.
	 * This method is invoked by [[Action]] when it begins to run with the given parameters.
	 * This method will check the parameter names that the action requires and return
	 * the provided parameters according to the requirement. If there is any missing parameter,
	 * an exception will be thrown.
	 * @param \yii\base\Action $action the action to be bound with parameters
	 * @param array $params the parameters to be bound to the action
	 * @return array the valid parameters that the action can run with.
	 * @throws HttpException if there are missing or invalid parameters.
	 */
	public function bindActionParams($action, $params)
	{
		if ($action instanceof InlineAction) {
			$method = new \ReflectionMethod($this, $action->actionMethod);
		} else {
			$method = new \ReflectionMethod($action, 'run');
		}

		$args = [];
		$missing = [];
		$actionParams = [];
		foreach ($method->getParameters() as $param) {
			$name = $param->getName();
			if (array_key_exists($name, $params)) {
				if ($param->isArray()) {
					$args[] = $actionParams[$name] = is_array($params[$name]) ? $params[$name] : [$params[$name]];
				} elseif (!is_array($params[$name])) {
					$args[] = $actionParams[$name] = $params[$name];
				} else {
					throw new BadRequestHttpException(Yii::t('yii', 'Invalid data received for parameter "{param}".', [
						'param' => $name,
					]));
				}
				unset($params[$name]);
			} elseif ($param->isDefaultValueAvailable()) {
				$args[] = $actionParams[$name] = $param->getDefaultValue();
			} else {
				$missing[] = $name;
			}
		}

		if (!empty($missing)) {
			throw new BadRequestHttpException(Yii::t('yii', 'Missing required parameters: {params}', [
				'params' => implode(', ', $missing),
			]));
		}

		$this->actionParams = $actionParams;

		return $args;
	}

	/**
	 * @inheritdoc
	 */
	public function beforeAction($action)
	{
		if (parent::beforeAction($action)) {
			if ($this->enableCsrfValidation && Yii::$app->exception === null && !Yii::$app->getRequest()->validateCsrfToken()) {
				throw new BadRequestHttpException(Yii::t('yii', 'Unable to verify your data submission.'));
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Normalizes route making it suitable for UrlManager. Absolute routes are staying as is
	 * while relative routes are converted to absolute routes.
	 *
	 * A relative route is a route without a leading slash, such as "view", "post/view".
	 *
	 * - If the route is an empty string, the current [[route]] will be used;
	 * - If the route contains no slashes at all, it is considered to be an action ID
	 *   of the current controller and will be prepended with [[uniqueId]];
	 * - If the route has no leading slash, it is considered to be a route relative
	 *   to the current module and will be prepended with the module's uniqueId.
	 *
	 * @param string $route the route. This can be either an absolute route or a relative route.
	 * @return string normalized route suitable for UrlManager
	 */
	protected function getNormalizedRoute($route)
	{
		if (strpos($route, '/') === false) {
			// empty or an action ID
			$route = $route === '' ? $this->getRoute() : $this->getUniqueId() . '/' . $route;
		} elseif ($route[0] !== '/') {
			// relative to module
			$route = ltrim($this->module->getUniqueId() . '/' . $route, '/');
		}
		return $route;
	}

	/**
	 * Creates a relative URL using the given route and parameters.
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
	 * @return string the created relative URL
	 */
	public function createUrl($route, $params = [])
	{
		$route = $this->getNormalizedRoute($route);
		return Yii::$app->getUrlManager()->createUrl($route, $params);
	}

	/**
	 * Creates an absolute URL using the given route and parameters.
	 *
	 * This method enhances [[UrlManager::createAbsoluteUrl()]] by supporting relative routes.
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
	 * @return string the created absolute URL
	 */
	public function createAbsoluteUrl($route, $params = [])
	{
		$route = $this->getNormalizedRoute($route);
		return Yii::$app->getUrlManager()->createAbsoluteUrl($route, $params);
	}

	/**
	 * Returns the canonical URL of the currently requested page.
	 * The canonical URL is constructed using [[route]] and [[actionParams]]. You may use the following code
	 * in the layout view to add a link tag about canonical URL:
	 *
	 * ~~~
	 * $this->registerLinkTag(['rel' => 'canonical', 'href' => Yii::$app->controller->canonicalUrl]);
	 * ~~~
	 *
	 * @return string the canonical URL of the currently requested page
	 */
	public function getCanonicalUrl()
	{
		return Yii::$app->getUrlManager()->createAbsoluteUrl($this->getRoute(), $this->actionParams);
	}

	/**
	 * Redirects the browser to the specified URL.
	 * This method is a shortcut to [[Response::redirect()]].
	 *
	 * You can use it in an action by returning the [[Response]] directly:
	 *
	 * ```php
	 * // stop executing this action and redirect to login page
	 * return $this->redirect(['login']);
	 * ```
	 *
	 * @param string|array $url the URL to be redirected to. This can be in one of the following formats:
	 *
	 * - a string representing a URL (e.g. "http://example.com")
	 * - a string representing a URL alias (e.g. "@example.com")
	 * - an array in the format of `[$route, ...name-value pairs...]` (e.g. `['site/index', 'ref' => 1]`)
	 *   [[Html::url()]] will be used to convert the array into a URL.
	 *
	 * Any relative URL will be converted into an absolute one by prepending it with the host info
	 * of the current request.
	 *
	 * @param integer $statusCode the HTTP status code. Defaults to 302.
	 * See <http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html>
	 * for details about HTTP status code
	 * @return Response the current response object
	 */
	public function redirect($url, $statusCode = 302)
	{
		return Yii::$app->getResponse()->redirect(Html::url($url), $statusCode);
	}

	/**
	 * Redirects the browser to the home page.
	 *
	 * You can use this method in an action by returning the [[Response]] directly:
	 *
	 * ```php
	 * // stop executing this action and redirect to home page
	 * return $this->goHome();
	 * ```
	 *
	 * @return Response the current response object
	 */
	public function goHome()
	{
		return Yii::$app->getResponse()->redirect(Yii::$app->getHomeUrl());
	}

	/**
	 * Redirects the browser to the last visited page.
	 *
	 * You can use this method in an action by returning the [[Response]] directly:
	 *
	 * ```php
	 * // stop executing this action and redirect to last visited page
	 * return $this->goBack();
	 * ```
	 *
	 * @param string|array $defaultUrl the default return URL in case it was not set previously.
	 * If this is null and the return URL was not set previously, [[Application::homeUrl]] will be redirected to.
	 * Please refer to [[User::setReturnUrl()]] on accepted format of the URL.
	 * @return Response the current response object
	 * @see User::getReturnUrl()
	 */
	public function goBack($defaultUrl = null)
	{
		return Yii::$app->getResponse()->redirect(Yii::$app->getUser()->getReturnUrl($defaultUrl));
	}

	/**
	 * Refreshes the current page.
	 * This method is a shortcut to [[Response::refresh()]].
	 *
	 * You can use it in an action by returning the [[Response]] directly:
	 *
	 * ```php
	 * // stop executing this action and refresh the current page
	 * return $this->refresh();
	 * ```
	 *
	 * @param string $anchor the anchor that should be appended to the redirection URL.
	 * Defaults to empty. Make sure the anchor starts with '#' if you want to specify it.
	 * @return Response the response object itself
	 */
	public function refresh($anchor = '')
	{
		return Yii::$app->getResponse()->redirect(Yii::$app->getRequest()->getUrl() . $anchor);
	}
}
