<?php

namespace backstage\controllers;

use Yii;
use yii\web\Controller;
use common\models\LoginForm;

class SiteController extends Controller
{
	public function actionIndex()
	{
		echo $this->render('index');
	}

	public function actionLogin()
	{
		$model = new LoginForm();
		if ($this->populate($_POST, $model) && $model->login()) {
			Yii::$app->response->redirect(array('site/index'));
		} else {
			echo $this->render('login', array(
				'model' => $model,
			));
		}
	}

	public function actionLogout()
	{
		Yii::$app->getUser()->logout();
		Yii::$app->getResponse()->redirect(array('site/index'));
	}
}
