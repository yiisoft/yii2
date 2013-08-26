<?php

namespace frontend\controllers;

use Yii;
use yii\web\Controller;
use common\models\LoginForm;
use frontend\models\ContactForm;
use common\models\User;
use yii\web\HttpException;
use yii\helpers\Security;

class SiteController extends Controller
{
	public function actions()
	{
		return array(
			'captcha' => array(
				'class' => 'yii\captcha\CaptchaAction',
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
			Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
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

	public function actionSignup()
	{
		$model = new User();
		$model->setScenario('signup');
		if ($model->load($_POST) && $model->save()) {
			if (Yii::$app->getUser()->login($model)) {
				return $this->goHome();
			}
		}

		return $this->render('signup', array(
			'model' => $model,
		));
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
		return $this->render('requestPasswordResetToken', array(
			'model' => $model,
		));
	}

	public function actionResetPassword($token)
	{
		$model = User::find(array(
			'password_reset_token' => $token,
			'status' => User::STATUS_ACTIVE,
		));

		if (!$model) {
			throw new HttpException(400, 'Wrong password reset token.');
		}

		$model->scenario = 'resetPassword';
		if ($model->load($_POST) && $model->save()) {
			Yii::$app->getSession()->setFlash('success', 'New password was saved.');
			return $this->goHome();
		}

		return $this->render('resetPassword', array(
			'model' => $model,
		));
	}

	private function sendPasswordResetEmail($email)
	{
		$user = User::find(array(
			'status' => User::STATUS_ACTIVE,
			'email' => $email,
		));

		if (!$user) {
			return false;
		}

		$user->password_reset_token = Security::generateRandomKey();
		if ($user->save(false)) {
			$fromEmail = \Yii::$app->params['supportEmail'];
			$name = '=?UTF-8?B?' . base64_encode(\Yii::$app->name . ' robot') . '?=';
			$subject = '=?UTF-8?B?' . base64_encode('Password reset for ' . \Yii::$app->name) . '?=';
			$body = $this->renderPartial('/emails/passwordResetToken', array(
				'user' => $user,
			));
			$headers = "From: $name <{$fromEmail}>\r\n" .
				"MIME-Version: 1.0\r\n" .
				"Content-type: text/plain; charset=UTF-8";
			return mail($fromEmail, $subject, $body, $headers);
		}

		return false;
	}
}
