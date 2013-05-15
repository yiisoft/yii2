<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Toolbar extends Widget
{
	public $debugAction = 'debug/default/toolbar';

	public function run()
	{
		if (Yii::$app->hasModule('debug')) {
			$id = 'yii-debug-toolbar';
			$url = Yii::$app->getUrlManager()->createUrl($this->debugAction, array(
				'tag' => Yii::getLogger()->tag,
			));
			$this->view->registerJs("yii.debug.load('$id', '$url');");
			$this->view->registerAssetBundle('yii/debug');
			echo Html::tag('div', '', array(
				'id' => $id,
				'style' => 'display: none',
			));
		}
	}
}
