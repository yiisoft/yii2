<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\debug\controllers;

use Yii;
use yii\web\Controller;
use yii\web\HttpException;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DefaultController extends Controller
{
	/** @var  \yii\debug\Module */
	public $module;
	public $layout = 'main';

	public function actionIndex($tag, $panel = null)
	{
		$this->loadData($tag);
		if (isset($this->module->panels[$panel])) {
			$activePanel = $this->module->panels[$panel];
		} else {
			$activePanel = reset($this->module->panels);
		}
		return $this->render('index', array(
			'tag' => $tag,
			'panels' => $this->module->panels,
			'activePanel' => $activePanel,
		));
	}

	public function actionToolbar($tag)
	{
		$this->loadData($tag);
		return $this->renderPartial('toolbar', array(
			'tag' => $tag,
			'panels' => $this->module->panels,
		));
	}

	protected function loadData($tag)
	{
		$file = Yii::$app->getRuntimePath() . "/debug/$tag.log";
		if (preg_match('/^[\w\-]+$/', $tag) && is_file($file)) {
			$data = json_decode(file_get_contents($file), true);
			foreach ($this->module->panels as $id => $panel) {
				if (isset($data[$id])) {
					$panel->load($data[$id]);
				} else {
					// remove the panel since it has not received any data
					unset($this->module->panels[$id]);
				}
			}
		} else {
			throw new HttpException(404, "Unable to find debug data tagged with '$tag'.");
		}
	}
}
