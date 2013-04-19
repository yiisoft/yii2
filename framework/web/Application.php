<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

use Yii;

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
	 * Processes the request.
	 * @return integer the exit status of the controller action (0 means normal, non-zero values mean abnormal)
	 */
	public function processRequest()
	{
		$request = $this->getRequest();
		Yii::setAlias('@wwwroot', dirname($request->getScriptFile()));
		Yii::setAlias('@www', $request->getBaseUrl());
		list ($route, $params) = $request->resolve();
		return $this->runAction($route, $params);
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
