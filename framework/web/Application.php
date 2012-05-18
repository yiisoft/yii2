<?php
/**
 * Application class file.
 *
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2012 Yii Software LLC
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
	 * Processes the request.
	 * @return integer the exit status of the controller action (0 means normal, non-zero values mean abnormal)
	 */
	public function processRequest()
	{
		$route = $this->resolveRequest();
		return $this->runController($route, null);
	}

	protected function resolveRequest()
	{
		return array();
	}
}
