<?php
namespace common\models\forms;

use common\models\User;
use yii\base\Model;
use yii\helpers\Security;
use Yii;

/**
 * Signup form
 */
class SignupForm extends Model
{
	public $username;
	public $email;
	public $password;

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			['username', 'filter', 'filter' => 'trim'],
			['username', 'required'],
			['username', 'string', 'min' => 2, 'max' => 255],

			['email', 'filter', 'filter' => 'trim'],
			['email', 'required'],
			['email', 'email'],
			['email', 'unique', 'targetClass' => 'User', 'message' => 'This email address has already been taken.'],

			['password', 'required'],
			['password', 'string', 'min' => 6],
		];
	}

	/**
	 * Signs user up.
	 * @return User saved model
	 */
	public function signup()
	{
		if ($this->validate()) {
			$user = new User();
			$user->username = $this->username;
			$user->email = $this->email;
			$user->password_hash = Security::generatePasswordHash($this->password);
			$user->auth_key = Security::generateRandomKey();
			$user->role = User::ROLE_USER;
			$user->status = USer::STATUS_ACTIVE;
			if ($user->save()) {
				return $user;
			}
		}
		return null;
	}
}
 