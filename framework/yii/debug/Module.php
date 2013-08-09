<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug;

use Yii;
use yii\base\View;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Module extends \yii\base\Module
{
	/**
	 * @var array the list of IPs that are allowed to access this module.
	 * Each array element represents a single IP filter which can be either an IP address
	 * or an address with wildcard (e.g. 192.168.0.*) to represent a network segment.
	 * The default value is `array('127.0.0.1', '::1')`, which means the module can only be accessed
	 * by localhost.
	 */
	public $allowedIPs = array('127.0.0.1', '::1');

	public $controllerNamespace = 'yii\debug\controllers';
	/**
	 * @var LogTarget
	 */
	public $logTarget;
	/**
	 * @var array|Panel[]
	 */
	public $panels = array();
	/**
	 * @var string the directory storing the debugger data files. This can be specified using a path alias.
	 */
	public $dataPath = '@runtime/debug';
	public $historySize = 50;
	public $enabled = true;

	public function init()
	{
		parent::init();
		if (!$this->enabled) {
			return;
		}
		$this->dataPath = Yii::getAlias($this->dataPath);
		$this->logTarget = Yii::$app->getLog()->targets['debug'] = new LogTarget($this);
		Yii::$app->getView()->on(View::EVENT_END_BODY, array($this, 'renderToolbar'));

		foreach (array_merge($this->corePanels(), $this->panels) as $id => $config) {
			$config['module'] = $this;
			$config['id'] = $id;
			$this->panels[$id] = Yii::createObject($config);
		}
	}

	public function beforeAction($action)
	{
		Yii::$app->getView()->off(View::EVENT_END_BODY, array($this, 'renderToolbar'));
		unset(Yii::$app->getLog()->targets['debug']);
		$this->logTarget = null;

		$ip = Yii::$app->getRequest()->getUserIP();
		foreach ($this->allowedIPs as $filter) {
			if ($filter === '*' || $filter === $ip || (($pos = strpos($filter, '*')) !== false && !strncmp($ip, $filter, $pos))) {
				return parent::beforeAction($action);
			}
		}
		return false;
	}

	public function renderToolbar($event)
	{
		$url = Yii::$app->getUrlManager()->createUrl($this->id . '/default/toolbar', array(
			'tag' => $this->logTarget->tag,
		));
		echo '<div id="yii-debug-toolbar" data-url="' . $url . '" style="display:none"></div>';
		/** @var View $view */
		$view = $event->sender;
		echo '<style>' . $view->renderPhpFile(__DIR__ . '/views/default/toolbar.css') . '</style>';
		echo '<script>' . $view->renderPhpFile(__DIR__ . '/views/default/toolbar.js') . '</script>';
	}

	protected function corePanels()
	{
		return array(
			'config' => array(
				'class' => 'yii\debug\panels\ConfigPanel',
			),
			'request' => array(
				'class' => 'yii\debug\panels\RequestPanel',
			),
			'log' => array(
				'class' => 'yii\debug\panels\LogPanel',
			),
			'profiling' => array(
				'class' => 'yii\debug\panels\ProfilingPanel',
			),
			'db' => array(
				'class' => 'yii\debug\panels\DbPanel',
			),
		);
	}
}
