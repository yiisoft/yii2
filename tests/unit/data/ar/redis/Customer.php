<?php

namespace yiiunit\data\ar\redis;

use yiiunit\extensions\redis\ActiveRecordTest;

class Customer extends ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

    public $status2;

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        return ['id', 'email', 'name', 'address', 'status', 'profile_id'];
    }

    /**
     * @return \yii\redis\ActiveQuery
     */
    public function getOrders()
    {
        return $this->hasMany(Order::className(), ['customer_id' => 'id']);
    }

    /**
     * @return \yii\redis\ActiveQuery
     */
    public function getExpensiveOrders()
    {
        return $this->hasMany(Order::className(), ['customer_id' => 'id'])->andWhere("tonumber(redis.call('HGET','order' .. ':a:' .. pk, 'total')) > 50");
    }

    /**
     * @return \yii\redis\ActiveQuery
     */
    public function getExpensiveOrdersWithNullFK()
    {
        return $this->hasMany(OrderWithNullFK::className(), ['customer_id' => 'id'])->andWhere("tonumber(redis.call('HGET','order' .. ':a:' .. pk, 'total')) > 50");
    }

    /**
     * @return \yii\redis\ActiveQuery
     */
    public function getOrdersWithNullFK()
    {
        return $this->hasMany(OrderWithNullFK::className(), ['customer_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        ActiveRecordTest::$afterSaveInsert = $insert;
        ActiveRecordTest::$afterSaveNewRecord = $this->isNewRecord;
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @inheritdoc
     * @return CustomerQuery
     */
    public static function find()
    {
        return new CustomerQuery(get_called_class());
    }
}
