<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\gii\controllers;

use yii\web\Controller;
use yii\web\HttpException;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class DefaultController extends Controller
{
	public $layout = 'generator';
	public $generator;

	public function actionIndex()
	{
		$this->layout = 'main';
		return $this->render('index');
	}

	public function actionView($id)
	{
		$generator = $this->loadGenerator($id);
		return $this->render('view', array(
			'generator' => $generator
		));
	}

	public function actionCode($file)
	{

	}

	public function actionDiff($file1, $file2)
	{

	}

	protected function loadGenerator($id)
	{
		if (isset($this->module->generators[$id])) {
			return $this->generator = $this->module->generators[$id];
		} else {
			throw new HttpException(404, "Code generator not found: $id");
		}
	}
}
