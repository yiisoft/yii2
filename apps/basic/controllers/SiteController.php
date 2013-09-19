<?php

namespace app\controllers;

use Yii;
use yii\web\AccessControl;
use yii\web\Controller;
use yii\web\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
	public function behaviors()
	{
		return array(
			'access' => array(
				'class' => AccessControl::className(),
				'only' => array('login', 'logout'),
				'rules' => array(
					array(
						'actions' => array('login'),
						'allow' => true,
						'roles' => array('?'),
					),
					array(
						'actions' => array('logout'),
						'allow' => true,
						'roles' => array('@'),
					),
				),
			),
			'verbs' => array(
				'class' => VerbFilter::className(),
				'actions' => array(
					'logout' => array('post'),
				),
			),
		);
	}

	public function actions()
	{
		return array(
			'error' => array(
				'class' => 'yii\web\ErrorAction',
			),
			'captcha' => array(
				'class' => 'yii\captcha\CaptchaAction',
				'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
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
			return $this->goHome();
		} else {
			return $this->render('login', array(
				'model' => $model,
			));
		}
	}

	public function actionLogout()
	{
		Yii::$app->user->logout();
		return $this->goHome();
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
