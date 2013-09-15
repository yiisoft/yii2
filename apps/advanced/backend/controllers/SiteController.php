<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use common\models\LoginForm;

class SiteController extends Controller
{
	public function behaviors()
	{
		return array(
			'access' => array(
				'class' => \yii\web\AccessControl::className(),
				'rules' => array(
					array(
						'actions' => array('login'),
						'allow' => true,
						'roles' => array('?'),
					),
					array(
						'actions' => array('logout', 'index'),
						'allow' => true,
						'roles' => array('@'),
					),
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
}
