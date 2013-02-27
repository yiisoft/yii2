<?php
/**
 * Application class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\web;

/**
 * Application is the base class for all application classes.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Application extends \yii\base\Application
{
	/**
	 * Sets default path aliases.
	 */
	public function registerDefaultAliases()
	{
		parent::registerDefaultAliases();
		\Yii::$aliases['@www'] = dirname($_SERVER['SCRIPT_FILENAME']);
	}

	/**
	 * Processes the request.
	 * @return integer the exit status of the controller action (0 means normal, non-zero values mean abnormal)
	 */
	public function processRequest()
	{
		$route = $this->getUrlManager()->parseRequest($this->getRequest());
		return $this->runAction($route, $_GET);
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
	 * @return UrlManager
	 */
	public function getUrlManager()
	{
		return $this->getComponent('urlManager');
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
			'urlManager' => array(
				'class' => 'yii\web\UrlManager',
			),
		));
	}
}
