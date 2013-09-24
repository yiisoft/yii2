<?php

namespace app\models;

class User extends \yii\base\Object implements \yii\web\IdentityInterface
{
	public $id;
	public $username;
	public $password;
	public $authKey;

	private static $users = array(
		'100' => array(
			'id' => '100',
			'username' => 'admin',
			'password' => 'admin',
			'authKey' => 'test100key',
		),
		'101' => array(
			'id' => '101',
			'username' => 'demo',
			'password' => 'demo',
			'authKey' => 'test101key',
		),
	);

	public static function findIdentity($id)
	{
		return isset(self::$users[$id]) ? new self(self::$users[$id]) : null;
	}

	public static function findByUsername($username)
	{
		foreach (self::$users as $user) {
			if (strcasecmp($user['username'], $username) === 0) {
				return new self($user);
			}
		}
		return null;
	}

	public function getId()
	{
		return $this->id;
	}

	public function getAuthKey()
	{
		return $this->authKey;
	}

	public function validateAuthKey($authKey)
	{
		return $this->authKey === $authKey;
	}

	public function validatePassword($password)
	{
		return $this->password === $password;
	}
}
