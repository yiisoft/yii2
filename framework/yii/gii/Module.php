<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\gii;

use Yii;
use yii\web\HttpException;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Module extends \yii\base\Module
{
	public $controllerNamespace = 'yii\gii\controllers';
	/**
	 * @var array the list of IPs that are allowed to access this module.
	 * Each array element represents a single IP filter which can be either an IP address
	 * or an address with wildcard (e.g. 192.168.0.*) to represent a network segment.
	 * The default value is `array('127.0.0.1', '::1')`, which means the module can only be accessed
	 * by localhost.
	 */
	public $allowedIPs = array('127.0.0.1', '::1');
	/**
	 * @var array a list of path aliases that refer to the directories containing code generators.
	 * The directory referred by a single path alias may contain multiple code generators, each stored
	 * under a sub-directory whose name is the generator name.
	 */
	public $generators = array();
	/**
	 * @var integer the permission to be set for newly generated code files.
	 * This value will be used by PHP chmod function.
	 * Defaults to 0666, meaning the file is read-writable by all users.
	 */
	public $newFileMode = 0666;
	/**
	 * @var integer the permission to be set for newly generated directories.
	 * This value will be used by PHP chmod function.
	 * Defaults to 0777, meaning the directory can be read, written and executed by all users.
	 */
	public $newDirMode = 0777;
	public $enabled = true;


	/**
	 * Initializes the gii module.
	 */
	public function init()
	{
		parent::init();
		foreach (array_merge($this->coreGenerators(), $this->generators) as $id => $config) {
			$this->generators[$id] = Yii::createObject($config);
		}
	}

	public function beforeAction($action)
	{
		if ($this->checkAccess()) {
			return parent::beforeAction($action);
		} else {
			throw new HttpException(403, 'You are not allowed to access this page.');
		}
	}

	protected function checkAccess()
	{
		$ip = Yii::$app->getRequest()->getUserIP();
		foreach ($this->allowedIPs as $filter) {
			if ($filter === '*' || $filter === $ip || (($pos = strpos($filter, '*')) !== false && !strncmp($ip, $filter, $pos))) {
				return true;
			}
		}
		return false;
	}

	protected function coreGenerators()
	{
		return array(
			'model' => array(
				'class' => 'yii\gii\generators\model\Generator',
			),
			'crud' => array(
				'class' => 'yii\gii\generators\crud\Generator',
			),
			'controller' => array(
				'class' => 'yii\gii\generators\controller\Generator',
			),
			'form' => array(
				'class' => 'yii\gii\generators\form\Generator',
			),
			'module' => array(
				'class' => 'yii\gii\generators\module\Generator',
			),
		);
	}
}
