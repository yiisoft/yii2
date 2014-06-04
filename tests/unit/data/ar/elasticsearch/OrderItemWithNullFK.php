<?php

namespace yiiunit\data\ar\elasticsearch;

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
    public function attributes()
    {
        return ['order_id', 'item_id', 'quantity', 'subtotal'];
    }

    public static function tableName()
    {
        return 'order_item_with_null_fk';
    }
}
