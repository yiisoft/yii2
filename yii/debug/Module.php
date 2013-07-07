<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug;

use Yii;
use yii\base\View;
use yii\helpers\Html;

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
	public $historySize = 5;

	public function init()
	{
		parent::init();

		$this->dataPath = Yii::getAlias($this->dataPath);
		$this->logTarget = Yii::$app->getLog()->targets['debug'] = new LogTarget($this);
		Yii::$app->getView()->on(View::EVENT_END_BODY, array($this, 'renderToolbar'));

		foreach (array_merge($this->corePanels(), $this->panels) as $id => $config) {
			$config['module'] = $this;
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
		/** @var View $view */
		$id = 'yii-debug-toolbar';
		$tag = $this->logTarget->tag;
		$url = Yii::$app->getUrlManager()->createUrl('debug/default/toolbar', array(
			'tag' => $tag,
		));
		$view = $event->sender;
		$view->registerJs("yii.debug.load('$id', '$url');");
		$view->registerAssetBundle('yii/debug');
		echo Html::tag('div', '', array(
			'id' => $id,
			'style' => 'display: none',
		));
	}

	protected function corePanels()
	{
		return array(
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
			'config' => array(
				'class' => 'yii\debug\panels\ConfigPanel',
			),
		);
	}
}
