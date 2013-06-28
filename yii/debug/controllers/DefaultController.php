<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\controllers;

use Yii;
use yii\web\Controller;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DefaultController extends Controller
{
	public $layout = 'main';

	public function actionIndex($tag)
	{
		return $this->render('index');
	}

	public function actionToolbar($tag)
	{
		$file = Yii::$app->getRuntimePath() . "/debug/$tag.log";
		if (preg_match('/^[\w\-]+$/', $tag) && is_file($file)) {
			$data = json_decode(file_get_contents($file), true);
			$data['tag'] = $tag;
			return $this->renderPartial('toolbar', $data);
		} else {
			return "Unable to find debug data tagged with '$tag'.";
		}
	}
}
