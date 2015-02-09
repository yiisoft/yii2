<?php

namespace yiiunit\data\ar\redis;

class Order extends ActiveRecord
{
    public function attributes()
    {
        return ['id', 'customer_id', 'created_at', 'total'];
    }

    public function getCustomer()
    {
        return $this->hasOne(Customer::className(), ['id' => 'customer_id']);
    }

    public function getOrderItems()
    {
        return $this->hasMany(OrderItem::className(), ['order_id' => 'id']);
    }

    public function getItems()
    {
        return $this->hasMany(Item::className(), ['id' => 'item_id'])
            ->via('orderItems', function ($q) {
                // additional query configuration
            });
    }

    public function getItemsIndexed()
    {
        return $this->hasMany(Item::className(), ['id' => 'item_id'])
            ->via('orderItems')->indexBy('id');
    }

    public function getItemsWithNullFK()
    {
        return $this->hasMany(Item::className(), ['id' => 'item_id'])
            ->via('orderItemsWithNullFK');
    }

    public function getOrderItemsWithNullFK()
    {
        return $this->hasMany(OrderItemWithNullFK::className(), ['order_id' => 'id']);
    }

    public function getItemsInOrder1()
    {
        return $this->hasMany(Item::className(), ['id' => 'item_id'])
            ->via('orderItems', function ($q) {
                $q->orderBy(['subtotal' => SORT_ASC]);
            })->orderBy('name');
    }

    public function getItemsInOrder2()
    {
        return $this->hasMany(Item::className(), ['id' => 'item_id'])
            ->via('orderItems', function ($q) {
                $q->orderBy(['subtotal' => SORT_DESC]);
            })->orderBy('name');
    }

    public function getBooks()
    {
        return $this->hasMany(Item::className(), ['id' => 'item_id'])
            ->via('orderItems')
            ->where(['category_id' => 1]);
    }

    public function getBooksWithNullFK()
    {
        return $this->hasMany(Item::className(), ['id' => 'item_id'])
            ->via('orderItemsWithNullFK')
            ->where(['category_id' => 1]);
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->created_at = time();

            return true;
        } else {
            return false;
        }
    }
}
