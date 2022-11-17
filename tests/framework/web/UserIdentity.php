<?php
namespace yiiunit\framework\web;

use yii\base\Component;
use yii\base\NotSupportedException;
use yii\web\IdentityInterface;

class UserIdentity extends Component implements IdentityInterface
{
    private static $ids = [
        'user1' => 'ABCD1234',
        'user2' => 'ABCD1234',
        'user3' => 'ABCD1234',
    ];

    private $_id;

    private $_authKey;

    public static function reset()
    {
        static::$ids = [
            'user1' => 'ABCD1234',
            'user2' => 'ABCD1234',
            'user3' => 'ABCD1234',
        ];
    }

    public static function findIdentity($id)
    {
        if (isset(static::$ids[$id])) {
            $identitiy = new static();
            $identitiy->_id = $id;
            return $identitiy;
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
        return static::$ids[$this->_id];
    }

    public function setAuthKey($authKey)
    {
        static::$ids[$this->_id] = $authKey;
    }

    public function validateAuthKey($authKey)
    {
        return $authKey === static::$ids[$this->_id];
    }
}
