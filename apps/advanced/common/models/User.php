<?php
namespace common\models;

use yii\db\ActiveRecord;
use yii\helpers\SecurityHelper;
use yii\web\Identity;

/**
 * Class User
 * @package common\models
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $email
 * @property string $auth_key
 * @property integer $role
 * @property integer $status
 * @property integer $create_time
 * @property integer $update_time
 */
class User extends ActiveRecord implements Identity
{
	/**
	 * @var string the raw password. Used to collect password input and isn't saved in database
	 */
	public $password;

	const STATUS_DELETED = 0;
	const STATUS_ACTIVE = 10;

	const ROLE_USER = 10;

	public function behaviors()
	{
		return array(
			'timestamp' => array(
				'class' => 'yii\behaviors\AutoTimestamp',
				'attributes' => array(
					ActiveRecord::EVENT_BEFORE_INSERT => array('create_time', 'update_time'),
					ActiveRecord::EVENT_BEFORE_UPDATE => 'update_time',
				),
			),
		);
	}

	public static function findIdentity($id)
	{
		return static::find($id);
	}

	public static function findByUsername($username)
	{
		return static::find(array('username' => $username, 'status' => static::STATUS_ACTIVE));
	}

	public function getId()
	{
		return $this->id;
	}

	public function getAuthKey()
	{
		return $this->auth_key;
	}

	public function validateAuthKey($authKey)
	{
		return $this->auth_key === $authKey;
	}

	public function validatePassword($password)
	{
		return SecurityHelper::validatePassword($password, $this->password_hash);
	}

	public function rules()
	{
		return array(
			array('username', 'filter', 'filter' => 'trim'),
			array('username', 'required'),
			array('username', 'length', 'min' => 2, 'max' => 255),

			array('email', 'filter', 'filter' => 'trim'),
			array('email', 'required'),
			array('email', 'email'),
			array('email', 'unique', 'message' => 'This email address has already been taken.'),

			array('password', 'required'),
			array('password', 'length', 'min' => 6),
		);
	}

	public function scenarios()
	{
		return array(
			'signup' => array('username', 'email', 'password'),
			'login' => array('username', 'password'),
		);
	}

	public function beforeSave($insert)
	{
		if(parent::beforeSave($insert)) {
			if($this->isNewRecord) {
				if(!empty($this->password)) {
					$this->password_hash = SecurityHelper::generatePasswordHash($this->password);
				}
			}
			return true;
		}
		return false;
	}
}
