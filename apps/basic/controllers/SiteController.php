<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
	public function actions()
	{
		return array(
			'captcha' => array(
				'class' => 'yii\web\CaptchaAction',
				'fixedVerifyCode' => YII_ENV === 'test' ? 'testme' : null,
			),
		);
	}

	public function actionIndex()
	{
		return $this->render('index');
	}

	public function actionLogin()
	{
		$model = new LoginForm();
		if ($this->populate($_POST, $model) && $model->login()) {
			return Yii::$app->response->redirect(array('site/index'));
		} else {
			return $this->render('login', array(
				'model' => $model,
			));
		}
	}

	public function actionLogout()
	{
		Yii::$app->user->logout();
		return Yii::$app->response->redirect(array('site/index'));
	}

	public function actionContact()
	{
		$model = new ContactForm;
		if ($this->populate($_POST, $model) && $model->contact(Yii::$app->params['adminEmail'])) {
			Yii::$app->session->setFlash('contactFormSubmitted');
			return Yii::$app->response->refresh();
		} else {
			return $this->render('contact', array(
				'model' => $model,
			));
		}
	}

	public function actionAbout()
	{
		return $this->render('about');
	}
}
