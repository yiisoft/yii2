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
	/**
	 * @var \yii\gii\Module
	 */
	public $module;
	/**
	 * @var \yii\gii\Generator
	 */
	public $generator;

	public function actionIndex()
	{
		$this->layout = 'main';
		return $this->render('index');
	}

	public function actionView($id)
	{
		$generator = $this->loadGenerator($id);
		$params = array('generator' => $generator);
		if (isset($_POST['preview']) || isset($_POST['generate'])) {
			if ($generator->validate()) {
				$files = $generator->prepare();
				if (isset($_POST['generate'], $_POST['answers'])) {
					$params['result'] = $generator->save($files, $_POST['answers']);
				} else {
					$params['files'] = $files;
				}
			}
		}

		return $this->render('view', $params);
	}

	public function actionCode($file)
	{

	}

	public function actionDiff($file1, $file2)
	{

	}

	/**
	 * Loads the generator with the specified ID.
	 * @param string $id the ID of the generator to be loaded.
	 * @return \yii\gii\Generator the loaded generator
	 * @throws \yii\web\HttpException
	 */
	protected function loadGenerator($id)
	{
		if (isset($this->module->generators[$id])) {
			$this->generator = $this->module->generators[$id];
			$this->generator->load($_POST);
			return $this->generator;
		} else {
			throw new HttpException(404, "Code generator not found: $id");
		}
	}
}
