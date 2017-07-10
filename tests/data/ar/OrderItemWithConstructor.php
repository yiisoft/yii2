<?php

namespace yiiunit\data\ar;

/**
 * Class OrderItem
 *
 * @property int $order_id
 * @property int $item_id
 * @property int $quantity
 * @property string $subtotal
 */
class OrderItemWithConstructor extends ActiveRecord
{
    public static $tableName;

    public static function tableName()
    {
        return static::$tableName ?: 'order_item';
    }

    public function __construct($item_id, $quantity)
    {
        $this->item_id = $item_id;
        $this->quantity = $quantity;
        parent::__construct();
    }

    public static function instantiate($row = [])
    {
        return (new \ReflectionClass(static::className()))->newInstanceWithoutConstructor();
    }

    public function getOrder()
    {
        return $this->hasOne(OrderWithConstructor::className(), ['id' => 'order_id']);
    }
}
