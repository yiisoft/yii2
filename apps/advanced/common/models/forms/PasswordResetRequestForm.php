<?php
namespace common\models\forms;

use common\models\User;
use yii\base\Model;
use yii\helpers\Security;

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
			['email', 'exist', 'targetClass' => 'User', 'message' => 'There is no user with such email.'],
		];
	}

	/**
	 *
	 * @return boolean sends an email
	 */
	public function sendEmail()
	{
		$user = User::find([
			'status' => User::STATUS_ACTIVE,
			'email' => $this->email,
		]);

		if (!$user) {
			return false;
		}

		$user->password_reset_token = Security::generateRandomKey();
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
 