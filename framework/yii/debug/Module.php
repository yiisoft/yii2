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
	public $controllerNamespace = 'yii\debug\controllers';
	/**
	 * @var array|Panel[]
	 */
	public $panels = array();

	public function init()
	{
		parent::init();

		foreach (array_merge($this->corePanels(), $this->panels) as $id => $config) {
			$config['id'] = $id;
			$this->panels[$id] = Yii::createObject($config);
		}

		Yii::$app->getLog()->targets['debug'] = new LogTarget($this);
		Yii::$app->getView()->on(View::EVENT_END_BODY, array($this, 'renderToolbar'));
	}

	public function beforeAction($action)
	{
		Yii::$app->getView()->off(View::EVENT_END_BODY, array($this, 'renderToolbar'));
		unset(Yii::$app->getLog()->targets['debug']);
		return parent::beforeAction($action);
	}

	public function renderToolbar($event)
	{
		/** @var View $view */
		$id = 'yii-debug-toolbar';
		$url = Yii::$app->getUrlManager()->createUrl('debug/default/toolbar', array(
			'tag' => Yii::$app->getLog()->getTag(),
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
			'config' => array(
				'class' => 'yii\debug\panels\ConfigPanel',
			),
			'request' => array(
				'class' => 'yii\debug\panels\RequestPanel',
			),
			'log' => array(
				'class' => 'yii\debug\panels\LogPanel',
			),
		);
	}
}
