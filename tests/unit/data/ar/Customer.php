<?php
namespace yiiunit\data\ar;

use yii\db\ActiveQuery;
use yiiunit\framework\db\ActiveRecordTest;

/**
 * Class Customer
 *
 * @property integer $id
 * @property string $name
 * @property string $email
 * @property string $address
 * @property integer $status
 *
 * @method CustomerQuery|Customer|null find($q = null) static
 * @method CustomerQuery findBySql($sql, $params = []) static
 */
class Customer extends ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

    public $status2;

    public static function tableName()
    {
        return 'tbl_customer';
    }

    public function getProfile()
    {
        return $this->hasOne(Profile::className(), ['id' => 'profile_id']);
    }

    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['customer_id' => 'id'])->orderBy('id');
    }

    public function getOrders2()
    {
        return $this->hasMany(Order::className(), ['customer_id' => 'id'])->inverseOf('customer2')->orderBy('id');
    }

    // deeply nested table relation
    public function getOrderItems()
    {
        /** @var ActiveQuery $rel */
        $rel = $this->hasMany(Item::className(), ['id' => 'item_id']);

        return $rel->viaTable('tbl_order_item', ['order_id' => 'id'], function ($q) {
            /** @var ActiveQuery $q */
            $q->viaTable('tbl_order', ['customer_id' => 'id']);
        })->orderBy('id');
    }

    public function afterSave($insert)
    {
        ActiveRecordTest::$afterSaveInsert = $insert;
        ActiveRecordTest::$afterSaveNewRecord = $this->isNewRecord;
        parent::afterSave($insert);
    }

    public static function createQuery($config = [])
    {
        $config['modelClass'] = get_called_class();

        return new CustomerQuery($config);
    }
}
