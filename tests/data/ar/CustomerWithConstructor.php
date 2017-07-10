<?php
namespace yiiunit\data\ar;

use yiiunit\framework\db\ActiveRecordTest;

/**
 * Class Customer
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
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

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

    public static function instantiate($row = [])
    {
        return (new \ReflectionClass(static::className()))->newInstanceWithoutConstructor();
    }

    public function getProfile()
    {
        return $this->hasOne(ProfileWithConstructor::className(), ['id' => 'profile_id']);
    }

    public function afterSave($insert, $changedAttributes)
    {
        ActiveRecordTest::$afterSaveInsert = $insert;
        ActiveRecordTest::$afterSaveNewRecord = $this->isNewRecord;
        parent::afterSave($insert, $changedAttributes);
    }
}
