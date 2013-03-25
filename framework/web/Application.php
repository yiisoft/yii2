<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use yii\base\InvalidParamException;

/**
 * Application is the base class for all application classes.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Application extends \yii\base\Application
{
	/**
	 * @var string the default route of this application. Defaults to 'site'.
	 */
	public $defaultRoute = 'site';

	/**
	 * Sets default path aliases.
	 */
	public function registerDefaultAliases()
	{
		parent::registerDefaultAliases();
		\Yii::$aliases['@webroot'] = dirname($_SERVER['SCRIPT_FILENAME']);
	}

	/**
	 * Processes the request.
	 * @return integer the exit status of the controller action (0 means normal, non-zero values mean abnormal)
	 */
	public function processRequest()
	{
		list ($route, $params) = $this->getRequest()->resolve();
		return $this->runAction($route, $params);
	}

	/**
	 * Returns the request component.
	 * @return Request the request component
	 */
	public function getRequest()
	{
		return $this->getComponent('request');
	}

	/**
	 * Returns the response component.
	 * @return Response the response component
	 */
	public function getResponse()
	{
		return $this->getComponent('response');
	}

	/**
	 * Returns the session component.
	 * @return Session the session component
	 */
	public function getSession()
	{
		return $this->getComponent('session');
	}

	/**
	 * Creates a URL using the given route and parameters.
	 *
	 * This method first normalizes the given route by converting a relative route into an absolute one.
	 * A relative route is a route without a leading slash. It is considered to be relative to the currently
	 * requested route. If the route is an empty string, it stands for the route of the currently active
	 * [[controller]]. Otherwise, the [[Controller::uniqueId]] will be prepended to the route.
	 *
	 * After normalizing the route, this method calls [[\yii\web\UrlManager::createUrl()]]
	 * to create a relative URL.
	 *
	 * @param string $route the route. This can be either an absolute or a relative route.
	 * @param array $params the parameters (name-value pairs) to be included in the generated URL
	 * @return string the created URL
	 * @throws InvalidParamException if a relative route is given and there is no active controller.
	 * @see createAbsoluteUrl
	 */
	public function createUrl($route, $params = array())
	{
		if (strncmp($route, '/', 1) !== 0) {
			// a relative route
			if ($this->controller !== null) {
				$route = $route === '' ? $this->controller->route : $this->controller->uniqueId . '/' . $route;
			} else {
				throw new InvalidParamException('Relative route cannot be handled because there is no active controller.');
			}
		}
		return $this->getUrlManager()->createUrl($route, $params);
	}

	/**
	 * Creates an absolute URL using the given route and parameters.
	 * This method first calls [[createUrl()]] to create a relative URL.
	 * It then prepends [[\yii\web\UrlManager::hostInfo]] to the URL to form an absolute one.
	 * @param string $route the route. This can be either an absolute or a relative route.
	 * See [[createUrl()]] for more details.
	 * @param array $params the parameters (name-value pairs)
	 * @return string the created URL
	 * @see createUrl
	 */
	public function createAbsoluteUrl($route, $params = array())
	{
		return $this->getUrlManager()->getHostInfo() . $this->createUrl($route, $params);
	}

	/**
	 * Registers the core application components.
	 * @see setComponents
	 */
	public function registerCoreComponents()
	{
		parent::registerCoreComponents();
		$this->setComponents(array(
			'request' => array(
				'class' => 'yii\web\Request',
			),
			'response' => array(
				'class' => 'yii\web\Response',
			),
			'session' => array(
				'class' => 'yii\web\Session',
			),
		));
	}
}
