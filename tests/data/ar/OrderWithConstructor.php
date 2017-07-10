<?php

namespace yiiunit\data\ar;

/**
 * Class Order
 *
 * @property int $id
 * @property int $customer_id
 * @property int $created_at
 * @property string $total
 *
 * @property OrderItemWithConstructor $orderItems
 * @property CustomerWithConstructor $customer
 * @property CustomerWithConstructor $customerJoinedWithProfile
 */
class OrderWithConstructor extends ActiveRecord
{
    public static $tableName;

    public static function tableName()
    {
        return static::$tableName ?: 'order';
    }

    public function __construct($id)
    {
        $this->id = $id;
        $this->created_at = time();
        parent::__construct();
    }

    public static function instantiate($row = [])
    {
        return (new \ReflectionClass(static::className()))->newInstanceWithoutConstructor();
    }

    public function getCustomer()
    {
        return $this->hasOne(CustomerWithConstructor::className(), ['id' => 'customer_id']);
    }

    public function getCustomerJoinedWithProfile()
    {
        return $this->hasOne(CustomerWithConstructor::className(), ['id' => 'customer_id'])
            ->joinWith('profile');
    }

    public function getOrderItems()
    {
        return $this->hasMany(OrderItemWithConstructor::className(), ['order_id' => 'id'])->inverseOf('order');
    }
}
