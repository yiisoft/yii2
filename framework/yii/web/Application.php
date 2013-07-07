<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;
use yii\base\InvalidRouteException;

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
	 * @var array the configuration specifying a controller action which should handle
	 * all user requests. This is mainly used when the application is in maintenance mode
	 * and needs to handle all incoming requests via a single action.
	 * The configuration is an array whose first element specifies the route of the action.
	 * The rest of the array elements (key-value pairs) specify the parameters to be bound
	 * to the action. For example,
	 *
	 * ~~~
	 * array(
	 *     'offline/notice',
	 *     'param1' => 'value1',
	 *     'param2' => 'value2',
	 * )
	 * ~~~
	 *
	 * Defaults to null, meaning catch-all is not effective.
	 */
	public $catchAll;
	/**
	 * @var Controller the currently active controller instance
	 */
	public $controller;


	/**
	 * Handles the specified request.
	 * @param Request $request the request to be handled
	 * @return Response the resulting response
	 * @throws HttpException if the requested route is invalid
	 */
	public function handleRequest($request)
	{
		Yii::setAlias('@wwwroot', dirname($request->getScriptFile()));
		Yii::setAlias('@www', $request->getBaseUrl());

		if (empty($this->catchAll)) {
			list ($route, $params) = $request->resolve();
		} else {
			$route = $this->catchAll[0];
			$params = array_splice($this->catchAll, 1);
		}
		try {
			Yii::trace("Route requested: '$route'", __METHOD__);
			$this->requestedRoute = $route;
			$result = $this->runAction($route, $params);
			if ($result instanceof Response) {
				return $result;
			} else {
				$response = $this->getResponse();
				if ($result !== null) {
					$response->data = $result;
				}
				return $response;
			}
		} catch (InvalidRouteException $e) {
			throw new HttpException(404, $e->getMessage(), $e->getCode(), $e);
		}
	}

	private $_homeUrl;

	/**
	 * @return string the homepage URL
	 */
	public function getHomeUrl()
	{
		if ($this->_homeUrl === null) {
			if ($this->getUrlManager()->showScriptName) {
				return $this->getRequest()->getScriptUrl();
			} else {
				return $this->getRequest()->getBaseUrl() . '/';
			}
		} else {
			return $this->_homeUrl;
		}
	}

	/**
	 * @param string $value the homepage URL
	 */
	public function setHomeUrl($value)
	{
		$this->_homeUrl = $value;
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
	 * Returns the user component.
	 * @return User the user component
	 */
	public function getUser()
	{
		return $this->getComponent('user');
	}

	/**
	 * Returns the asset manager.
	 * @return AssetManager the asset manager component
	 */
	public function getAssetManager()
	{
		return $this->getComponent('assetManager');
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
			'user' => array(
				'class' => 'yii\web\User',
			),
			'assetManager' => array(
				'class' => 'yii\web\AssetManager',
			),
		));
	}
}
