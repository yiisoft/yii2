<?php
namespace common\models\forms;

use common\models\User;
use yii\base\InvalidParamException;
use yii\base\Model;
use yii\helpers\Security;
use Yii;

/**
 * Password reset form
 */
class ResetPasswordForm extends Model
{
	public $password;

	/**
	 * @var \common\models\User
	 */
	private $_user;

	/**
	 * Creates a form model given a token
	 *
	 * @param string $token
	 * @throws \yii\base\InvalidParamException if token is empty or not valid
	 */
	public function __construct($token)
	{
		if (empty($token) || !is_string($token)) {
			throw new InvalidParamException('Password reset token cannot be blank.');
		}
		$this->_user = User::find([
			'password_reset_token' => $token,
			'status' => User::STATUS_ACTIVE,
		]);
		if (!$this->_user) {
			throw new InvalidParamException('Wrong password reset token.');
		}
	}

	/**
	 * @return array the validation rules.
	 */
	public function rules()
	{
		return [
			['password', 'required'],
			['password', 'string', 'min' => 6],
		];
	}

	/**
	 * Resets password.
	 * @return boolean if password was reset.
	 */
	public function resetPassword()
	{
		$user = $this->_user;
		if ($user->validate()) {
			$user->password_hash = Security::generatePasswordHash($this->password);
			$user->password_reset_token = '';
			return $user->save();
		} else {
			return false;
		}
	}
}
 