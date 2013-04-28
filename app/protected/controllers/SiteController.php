<?php

use app\models\LoginForm;
use app\models\User;

class SiteController extends \yii\web\Controller
{
	public function actionIndex()
	{
		echo $this->render('index');
	}

	public function actionLogin()
	{
		$model = new LoginForm();
		if (isset($_POST[$model->formName()])) {
			$model->attributes = $_POST[$model->formName()];
			if ($model->login()) {
				Yii::$app->getResponse()->redirect(array('site/index'));
			}
		}
		echo $this->render('login', array(
			'model' => $model,
		));
	}

	public function actionLogout()
	{
		Yii::$app->getUser()->logout();
		Yii::$app->getResponse()->redirect(array('site/index'));
	}
}