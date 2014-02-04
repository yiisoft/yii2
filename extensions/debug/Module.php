<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug;

use Yii;
use yii\base\Application;
use yii\web\View;
use yii\web\ForbiddenHttpException;
use yii\helpers\ArrayHelper;

/**
 * The Yii Debug Module provides the debug toolbar and debugger
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Module extends \yii\base\Module
{
	/**
	 * @var array the list of IPs that are allowed to access this module.
	 * Each array element represents a single IP filter which can be either an IP address
	 * or an address with wildcard (e.g. 192.168.0.*) to represent a network segment.
	 * The default value is `['127.0.0.1', '::1']`, which means the module can only be accessed
	 * by localhost.
	 */
	public $allowedIPs = ['127.0.0.1', '::1'];
	/**
	 * @var string the namespace that controller classes are in.
	 */
	public $controllerNamespace = 'yii\debug\controllers';
	/**
	 * @var LogTarget
	 */
	public $logTarget;
	/**
	 * @var array|Panel[]
	 */
	public $panels = [];
	/**
	 * @var string the directory storing the debugger data files. This can be specified using a path alias.
	 */
	public $dataPath = '@runtime/debug';
	/**
	 * @var integer the maximum number of debug data files to keep. If there are more files generated,
	 * the oldest ones will be removed.
	 */
	public $historySize = 50;

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		$this->dataPath = Yii::getAlias($this->dataPath);
		$this->logTarget = Yii::$app->getLog()->targets['debug'] = new LogTarget($this);
		// do not initialize view component before application is ready (needed when debug in preload)
		Yii::$app->on(Application::EVENT_BEFORE_REQUEST, function() {
			Yii::$app->getView()->on(View::EVENT_END_BODY, [$this, 'renderToolbar']);
		});

		$this->panels = ArrayHelper::merge($this->corePanels(), $this->panels);
		foreach ($this->panels as $id => $config) {
			$config['module'] = $this;
			$config['id'] = $id;
			$this->panels[$id] = Yii::createObject($config);
		}
	}

	/**
	 * @inheritdoc
	 */
	public function beforeAction($action)
	{
		Yii::$app->getView()->off(View::EVENT_END_BODY, [$this, 'renderToolbar']);
		unset(Yii::$app->getLog()->targets['debug']);
		$this->logTarget = null;

		if ($this->checkAccess()) {
			return parent::beforeAction($action);
		} elseif ($action->id === 'toolbar') {
			return false;
		} else {
			throw new ForbiddenHttpException('You are not allowed to access this page.');
		}
	}

	/**
	 * Renders mini-toolbar at the end of page body.
	 *
	 * @param \yii\base\Event $event
	 */
	public function renderToolbar($event)
	{
		if (!$this->checkAccess() || Yii::$app->getRequest()->getIsAjax()) {
			return;
		}
		$url = Yii::$app->getUrlManager()->createUrl($this->id . '/default/toolbar', [
			'tag' => $this->logTarget->tag,
		]);
		echo '<div id="yii-debug-toolbar" data-url="' . $url . '" style="display:none"></div>';
		/** @var View $view */
		$view = $event->sender;
		echo '<style>' . $view->renderPhpFile(__DIR__ . '/assets/toolbar.css') . '</style>';
		echo '<script>' . $view->renderPhpFile(__DIR__ . '/assets/toolbar.js') . '</script>';
	}

	/**
	 * Checks if current user is allowed to access the module
	 * @return boolean if access is granted
	 */
	protected function checkAccess()
	{
		$ip = Yii::$app->getRequest()->getUserIP();
		foreach ($this->allowedIPs as $filter) {
			if ($filter === '*' || $filter === $ip || (($pos = strpos($filter, '*')) !== false && !strncmp($ip, $filter, $pos))) {
				return true;
			}
		}
		Yii::warning('Access to debugger is denied due to IP address restriction. The requested IP is ' . $ip, __METHOD__);
		return false;
	}

	/**
	 * @return array default set of panels
	 */
	protected function corePanels()
	{
		return [
			'config' => ['class' => 'yii\debug\panels\ConfigPanel'],
			'request' => ['class' => 'yii\debug\panels\RequestPanel'],
			'log' => ['class' => 'yii\debug\panels\LogPanel'],
			'profiling' => ['class' => 'yii\debug\panels\ProfilingPanel'],
			'db' => ['class' => 'yii\debug\panels\DbPanel'],
		];
	}
}
