<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use common\models\LoginForm;
use frontend\models\ContactForm;

class SiteController extends Controller
{
	public function actions()
	{
		return array(
			'captcha' => array(
				'class' => 'yii\web\CaptchaAction',
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
		if ($model->load($_POST) && $model->login()) {
			return $this->redirect(array('site/index'));
		} else {
			return $this->render('login', array(
				'model' => $model,
			));
		}
	}

	public function actionLogout()
	{
		Yii::$app->user->logout();
		return $this->redirect(array('site/index'));
	}

	public function actionContact()
	{
		$model = new ContactForm;
		if ($model->load($_POST) && $model->contact(Yii::$app->params['adminEmail'])) {
			Yii::$app->session->setFlash('contactFormSubmitted');
			return $this->refresh();
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
