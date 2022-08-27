<?php
/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

namespace yiiunit\data\ar;

/**
 * OrderWithConstructor.
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
    public static function tableName()
    {
        return 'order';
    }

    public function __construct($id)
    {
        $this->id = $id;
        $this->created_at = time();
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
