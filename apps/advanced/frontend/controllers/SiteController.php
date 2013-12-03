<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use common\models\LoginForm;
use frontend\models\ContactForm;
use common\models\User;
use yii\web\BadRequestHttpException;
use yii\helpers\Security;

class SiteController extends Controller
{
	public function behaviors()
	{
		return [
			'access' => [
				'class' => \yii\web\AccessControl::className(),
				'only' => ['logout', 'signup'],
				'rules' => [
					[
						'actions' => ['signup'],
						'allow' => true,
						'roles' => ['?'],
					],
					[
						'actions' => ['logout'],
						'allow' => true,
						'roles' => ['@'],
					],
				],
			],
		];
	}

	public function actions()
	{
		return [
			'error' => [
				'class' => 'yii\web\ErrorAction',
			],
			'captcha' => [
				'class' => 'yii\captcha\CaptchaAction',
				'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
			],
		];
	}

	public function actionIndex()
	{
		return $this->render('index');
	}

	public function actionLogin()
	{
		if (!\Yii::$app->user->isGuest) {
			$this->goHome();
		}

		$model = new LoginForm();
		if ($model->load($_POST) && $model->login()) {
			return $this->goBack();
		} else {
			return $this->render('login', [
				'model' => $model,
			]);
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
			Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
			return $this->refresh();
		} else {
			return $this->render('contact', [
				'model' => $model,
			]);
		}
	}

	public function actionAbout()
	{
		return $this->render('about');
	}

	public function actionSignup()
	{
		$model = new User();
		$model->setScenario('signup');
		if ($model->load($_POST) && $model->save()) {
			if (Yii::$app->getUser()->login($model)) {
				return $this->goHome();
			}
		}

		return $this->render('signup', [
			'model' => $model,
		]);
	}

	public function actionRequestPasswordReset()
	{
		$model = new User();
		$model->scenario = 'requestPasswordResetToken';
		if ($model->load($_POST) && $model->validate()) {
			if ($this->sendPasswordResetEmail($model->email)) {
				Yii::$app->getSession()->setFlash('success', 'Check your email for further instructions.');
				return $this->goHome();
			} else {
				Yii::$app->getSession()->setFlash('error', 'There was an error sending email.');
			}
		}
		return $this->render('requestPasswordResetToken', [
			'model' => $model,
		]);
	}

	public function actionResetPassword($token)
	{
		$model = User::find([
			'password_reset_token' => $token,
			'status' => User::STATUS_ACTIVE,
		]);

		if (!$model) {
			throw new BadRequestHttpException('Wrong password reset token.');
		}

		$model->scenario = 'resetPassword';
		if ($model->load($_POST) && $model->save()) {
			Yii::$app->getSession()->setFlash('success', 'New password was saved.');
			return $this->goHome();
		}

		return $this->render('resetPassword', [
			'model' => $model,
		]);
	}

	private function sendPasswordResetEmail($email)
	{
		$user = User::find([
			'status' => User::STATUS_ACTIVE,
			'email' => $email,
		]);

		if (!$user) {
			return false;
		}

		$user->password_reset_token = Security::generateRandomKey();
		if ($user->save(false)) {
			return \Yii::$app->mail->compose('passwordResetToken', ['user' => $user])
				->setFrom([\Yii::$app->params['supportEmail'] => \Yii::$app->name . ' robot'])
				->setTo($email)
				->setSubject('Password reset for ' . \Yii::$app->name)
				->send();
		}

		return false;
	}
}
