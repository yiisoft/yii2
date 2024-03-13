<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\ar;

/**
 * CustomerWithConstructor.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $address
 * @property int $status
 *
 * @property ProfileWithConstructor $profile
 */
class CustomerWithConstructor extends ActiveRecord
{
    public static function tableName()
    {
        return 'customer';
    }

    public function __construct($name, $email, $address)
    {
        $this->name = $name;
        $this->email = $email;
        $this->address = $address;
        parent::__construct();
    }

    public static function instance($refresh = false)
    {
        return self::instantiate([]);
    }

    public static function instantiate($row)
    {
        return (new \ReflectionClass(static::className()))->newInstanceWithoutConstructor();
    }

    public function getProfile()
    {
        return $this->hasOne(ProfileWithConstructor::className(), ['id' => 'profile_id']);
    }
}
