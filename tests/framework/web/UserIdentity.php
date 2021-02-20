<?php
namespace yiiunit\framework\web;

use Yii;
use yii\base\Component;
use yii\base\NotSupportedException;
use yii\web\IdentityInterface;

class UserIdentity extends Component implements IdentityInterface
{
    private static $ids = [
        'user1',
        'user2',
        'user3',
    ];

    private static $authKeys = [
        'user1' => 'ABCD1234',
        'user2' => null,
        'user3' => 'DglpKZ1p9dHZS2VKvTHxxaiCHJIWZy4C',
    ];

    private $_id;

    private $_auth_key;

    public static function findIdentity($id)
    {
        if (in_array($id, static::$ids)) {
            $identity = new static();
            $identity->_id = $id;
            $identity->_auth_key = static::$authKeys[$id];
            return $identity;
        }
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException();
    }

    public function getId()
    {
        return $this->_id;
    }

    public function getAuthKey()
    {
        return $this->_auth_key;
    }

    public function validateAuthKey($authKey)
    {
        return $authKey === 'ABCD1234';
    }

    public function updateAuthKey(IdentityInterface $identity, $token)
    {
        $this->_auth_key = $token;
    }

    public function generateAuthKey()
    {
        return Yii::$app->security->generateRandomString(32);
    }
}
