<?php

namespace yiiunit\data\ar;

/**
 * Class OrderItem
 *
 * @property integer $order_id
 * @property integer $item_id
 * @property integer $quantity
 * @property string $subtotal
 */
class OrderItem extends ActiveRecord
{
    public static $tableName;

    public static function tableName()
    {
        return static::$tableName ?: 'order_item';
    }

    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    public function getItem()
    {
        return $this->hasOne(Item::className(), ['id' => 'item_id']);
    }

    // relations used by ::testFindCompositeWithJoin()
    public function getOrderItemCompositeWithJoin()
    {
        return $this->hasOne(OrderItem::className(), ['item_id' => 'item_id', 'order_id' => 'order_id' ])
            ->joinWith('item');
    }
    public function getOrderItemCompositeNoJoin()
    {
        return $this->hasOne(OrderItem::className(), ['item_id' => 'item_id', 'order_id' => 'order_id' ]);
    }
}
