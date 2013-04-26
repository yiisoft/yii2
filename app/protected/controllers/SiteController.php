<?php

class SiteController extends \yii\web\Controller
{
	public function actionIndex()
	{
		echo $this->render('index');
	}

	public function actionLogin()
	{
		echo $this->render('login');
//		$user = app\models\User::findIdentity(100);
//		Yii::$app->getUser()->login($user);
//		Yii::$app->getResponse()->redirect(array('site/index'));
	}

	public function actionLogout()
	{
		Yii::$app->getUser()->logout();
		Yii::$app->getResponse()->redirect(array('site/index'));
	}
}