<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use common\models\LoginForm;

class SiteController extends Controller
{
	public function actionIndex()
	{
		return $this->render('index');
	}

	public function actionLogin()
	{
		$model = new LoginForm();
		if ($this->populate($_POST, $model) && $model->login()) {
			return $this->response->redirect(array('site/index'));
		} else {
			return $this->render('login', array(
				'model' => $model,
			));
		}
	}

	public function actionLogout()
	{
		Yii::$app->user->logout();
		return $this->response->redirect(array('site/index'));
	}
}
