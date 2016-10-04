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
class OrderItemWithNullFK extends ActiveRecord
{
    public static function tableName()
    {
        return 'order_item_with_null_fk';
    }

}
