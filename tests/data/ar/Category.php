<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\data\ar;

/**
 * Class Category.
 *
 * @property int $id
 * @property string $name
 */
class Category extends ActiveRecord
{
    public static function tableName()
    {
        return 'category';
    }

    public function getItems()
    {
        return $this->hasMany(Item::class, ['category_id' => 'id']);
    }

    public function getLimitedItems()
    {
        return $this->hasMany(Item::class, ['category_id' => 'id'])
            ->onCondition(['item.id' => [1, 2, 3]]);
    }

    public function getOrderItems()
    {
        return $this->hasMany(OrderItem::class, ['item_id' => 'id'])->via('items');
    }

    public function getOrders()
    {
        return $this->hasMany(Order::class, ['id' => 'order_id'])->via('orderItems');
    }
}
