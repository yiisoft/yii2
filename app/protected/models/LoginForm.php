<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class LoginForm extends Model
{
	public $username;
	public $password;

	public function rules()
	{
		return array(
			array('username', 'required'),
			array('password', 'required'),
			array('password', 'validatePassword'),
		);
	}

	public function validatePassword()
	{
		$user = User::findByUsername($this->username);
		if (!$user || !$user->validatePassword($this->password)) {
			$this->addError('password', 'Incorrect username or password.');
		}
	}

	public function login()
	{
		if ($this->validate()) {
			$user = User::findByUsername($this->username);
			Yii::$app->getUser()->login($user);
			return true;
		} else {
			return false;
		}
	}
}