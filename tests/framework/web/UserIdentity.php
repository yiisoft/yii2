<?php
namespace yiiunit\framework\web;

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

    private $_id;

    public static function findIdentity($id)
    {
        if (in_array($id, static::$ids)) {
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
        return 'ABCD1234';
    }

    public function validateAuthKey($authKey)
    {
        return $authKey === 'ABCD1234';
    }
}
