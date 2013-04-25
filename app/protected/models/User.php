<?php

namespace app\models;

class User extends \yii\base\Object implements \yii\web\Identity
{
	public $id;
	public $name;
	public $authKey;

	private static $users = array(
		'100' => array(
			'id' => '100',
			'authKey' => 'test100key',
			'name' => 'admin',
		),
		'101' => array(
			'id' => '101',
			'authKey' => 'test101key',
			'name' => 'demo',
		),
	);

	public static function findIdentity($id)
	{
		return isset(self::$users[$id]) ? new self(self::$users[$id]) : null;
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
}