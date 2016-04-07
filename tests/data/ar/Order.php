<?php

namespace yiiunit\data\ar;

/**
 * Class Order
 *
 * @property integer $id
 * @property integer $customer_id
 * @property integer $created_at
 * @property string $total
 */
class Order extends ActiveRecord
{
    public static function tableName()
    {
        return 'order';
    }

    public function getCustomer()
    {
        return $this->hasOne(Customer::className(), ['id' => 'customer_id']);
    }

    public function getCustomer2()
    {
        return $this->hasOne(Customer::className(), ['id' => 'customer_id'])->inverseOf('orders2');
    }

    public function getOrderItems()
    {
        return $this->hasMany(OrderItem::className(), ['order_id' => 'id']);
    }

    public function getOrderItemsWithNullFK()
    {
        return $this->hasMany(OrderItemWithNullFK::className(), ['order_id' => 'id']);
    }

    public function getItems()
    {
        return $this->hasMany(Item::className(), ['id' => 'item_id'])
            ->via('orderItems', function ($q) {
                // additional query configuration
            })->orderBy('item.id');
    }

    public function getItemsIndexed()
    {
        return $this->hasMany(Item::className(), ['id' => 'item_id'])
            ->via('orderItems')->indexBy('id');
    }

    public function getItemsIndexed2()
    {
        return $this->hasMany(Item::className(), ['id' => 'item_id'])
            ->via('orderItems')->indexBy(function ($row) {
                return $row['id'];
            });
    }

    public function getItemsWithNullFK()
    {
        return $this->hasMany(Item::className(), ['id' => 'item_id'])
            ->viaTable('order_item_with_null_fk', ['order_id' => 'id']);
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

    public function getBooksViaTable()
    {
        return $this->hasMany(Item::className(), ['id' => 'item_id'])
            ->viaTable('order_item', ['order_id' => 'id'])
            ->where(['category_id' => 1]);
    }

    public function getBooksWithNullFKViaTable()
    {
        return $this->hasMany(Item::className(), ['id' => 'item_id'])
            ->viaTable('order_item_with_null_fk', ['order_id' => 'id'])
            ->where(['category_id' => 1]);
    }

    public function getBooks2()
    {
        return $this->hasMany(Item::className(), ['id' => 'item_id'])
            ->onCondition(['category_id' => 1])
            ->viaTable('order_item', ['order_id' => 'id']);
    }

    public function getBooksExplicit()
    {
        return $this->hasMany(Item::className(), ['id' => 'item_id'])
            ->onCondition(['category_id' => 1])
            ->viaTable('order_item', ['order_id' => 'id']);
    }

//    public function getBooksQuerysyntax()
//    {
//        return $this->hasMany(Item::className(), ['id' => 'item_id'])
//            ->onCondition(['{{@item}}.category_id' => 1])
//            ->viaTable('order_item', ['order_id' => 'id']);
//    }

    public function getBooksExplicitA()
    {
        return $this->hasMany(Item::className(), ['id' => 'item_id'])->alias('bo')
            ->onCondition(['bo.category_id' => 1])
            ->viaTable('order_item', ['order_id' => 'id']);
    }

//    public function getBooksQuerysyntaxA()
//    {
//        return $this->hasMany(Item::className(), ['id' => 'item_id'])->alias('bo')
//            ->onCondition(['{{@item}}.category_id' => 1])
//            ->viaTable('order_item', ['order_id' => 'id']);
//    }

    public function getBookItems()
    {
        return $this->hasMany(Item::className(), ['id' => 'item_id'])->alias('books')
            ->onCondition(['books.category_id' => 1])
            ->viaTable('order_item', ['order_id' => 'id']);
    }

    public function getMovieItems()
    {
        return $this->hasMany(Item::className(), ['id' => 'item_id'])->alias('movies')
            ->onCondition(['movies.category_id' => 2])
            ->viaTable('order_item', ['order_id' => 'id']);
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

    public function attributeLabels()
    {
        return [
            'customer_id' => 'Customer',
            'total' => 'Invoice Total',
        ];
    }
}
