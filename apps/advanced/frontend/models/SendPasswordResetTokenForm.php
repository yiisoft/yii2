<?php

namespace frontend\models;
use yii\base\Model;
use common\models\User;
use yii\base\View;
use yii\helpers\Security;

/**
 * SendPasswordResetTokenForm is the model behind requesting password reset token form.
 */
class SendPasswordResetTokenForm extends Model
{
	public $email;

	/**
	 * @return array the validation rules.
	 */
	public function rules()
	{
		return array(
			array('email', 'required'),
			array('email', 'email'),
		);
	}

	public function sendEmail()
	{
		if($this->validate()) {
			/** @var User $user */
			$user = User::find(array(
				'email' => $this->email,
				'status' => User::STATUS_ACTIVE,
			));
			if ($user) {
				$user->password_reset_token = Security::generateRandomKey();
				if ($user->save(false)) {
					$view = new View(array(
						'context' => \Yii::$app->controller,
					));

					$fromEmail = \Yii::$app->params['supportEmail'];
					$name = '=?UTF-8?B?' . base64_encode(\Yii::$app->name . ' robot') . '?=';
					$subject = '=?UTF-8?B?' . base64_encode('Password reset for ' . \Yii::$app->name) . '?=';
					$body = $view->render('/emails/passwordResetToken', array(
						'user' => $user,
					));
					$headers = "From: $name <{$fromEmail}>\r\n" .
						"MIME-Version: 1.0\r\n" .
						"Content-type: text/plain; charset=UTF-8";
					mail($fromEmail, $subject, $body, $headers);
					return true;
				}
			}
		}

		return false;
	}
}
