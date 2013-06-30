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
	public $panels;

	public function init()
	{
		parent::init();
		Yii::$app->log->targets['debug'] = new LogTarget;
		Yii::$app->getView()->on(View::EVENT_END_BODY, array($this, 'renderToolbar'));
	}

	public function beforeAction($action)
	{
		Yii::$app->getView()->off(View::EVENT_END_BODY, array($this, 'renderToolbar'));
		unset(Yii::$app->log->targets['debug']);
		return parent::beforeAction($action);
	}

	public function renderToolbar($event)
	{
		/** @var View $view */
		$id = 'yii-debug-toolbar';
		$url = Yii::$app->getUrlManager()->createUrl('debug/default/toolbar', array(
			'tag' => Yii::getLogger()->tag,
		));
		$view = $event->sender;
		$view->registerJs("yii.debug.load('$id', '$url');");
		$view->registerAssetBundle('yii/debug');
		echo Html::tag('div', '', array(
			'id' => $id,
			'style' => 'display: none',
		));
	}
}
