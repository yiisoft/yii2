<?php

namespace yiiunit\data\ar\redis;

/**
 * Class OrderItem
 *
 * @property integer $order_id
 * @property integer $item_id
 * @property integer $quantity
 * @property string $subtotal
 */
class OrderItemWithNullFK extends ActiveRecord
{
    public static function primaryKey()
    {
        return ['order_id', 'item_id'];
    }

    public function attributes()
    {
        return ['order_id', 'item_id', 'quantity', 'subtotal'];
    }
}
