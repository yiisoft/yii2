<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug;

use Yii;
use yii\base\Component;
use yii\base\View;
use yii\helpers\Html;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Debugger extends Component
{
	public $debugAction = 'debug/default/toolbar';
	public $panels;

	public function init()
	{
		parent::init();
		Yii::$app->setModule('debug', array(
			'class' => 'yii\debug\Module',
			'panels' => $this->panels,
		));
		Yii::$app->log->targets[] = new LogTarget;
		Yii::$app->getView()->on(View::EVENT_END_BODY, array($this, 'renderToolbar'));
	}

	public function renderToolbar($event)
	{
		if (Yii::$app->getModule('debug', false) !== null) {
			return;
		}

		/** @var View $view */
		$id = 'yii-debug-toolbar';
		$url = Yii::$app->getUrlManager()->createUrl($this->debugAction, array(
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
