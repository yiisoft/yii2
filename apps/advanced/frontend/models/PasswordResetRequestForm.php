<?php
namespace frontend\models;

use common\models\User;
use yii\base\Model;

/**
 * Password reset request form
 */
class PasswordResetRequestForm extends Model
{
	public $email;

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			['email', 'filter', 'filter' => 'trim'],
			['email', 'required'],
			['email', 'email'],
			['email', 'exist', 'targetClass' => '\common\models\User', 'message' => 'There is no user with such email.'],
		];
	}

	/**
	 * Sends an email with a link, for resetting the password.
	 *
	 * @return boolean whether the email was send
	 */
	public function sendEmail()
	{
		/** @var User $user */
		$user = User::find([
			'status' => User::STATUS_ACTIVE,
			'email' => $this->email,
		]);

		if (!$user) {
			return false;
		}

		$user->generatePasswordResetToken();
		if ($user->save()) {
			return \Yii::$app->mail->compose('passwordResetToken', ['user' => $user])
				->setFrom([\Yii::$app->params['supportEmail'] => \Yii::$app->name . ' robot'])
				->setTo($this->email)
				->setSubject('Password reset for ' . \Yii::$app->name)
				->send();
		}

		return false;
	}
}