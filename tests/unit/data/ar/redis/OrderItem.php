<?php

namespace yiiunit\data\ar\redis;

class OrderItem extends ActiveRecord
{
    public static function primaryKey()
    {
        return ['order_id', 'item_id'];
    }

    public function attributes()
    {
        return ['order_id', 'item_id', 'quantity', 'subtotal'];
    }

    public function getOrder()
    {
        return $this->hasOne(Order::className(), ['id' => 'order_id']);
    }

    public function getItem()
    {
        return $this->hasOne(Item::className(), ['id' => 'item_id']);
    }
}
