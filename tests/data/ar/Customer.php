<?php

/**
 * @link https://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license https://www.yiiframework.com/license/
 */

declare(strict_types=1);

namespace yiiunit\data\ar;

use yii\db\ActiveQuery;
use yiiunit\framework\db\ActiveRecordTest;

/**
 * Class Customer.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $address
 * @property int $status
 * @property 1|0|'1'|'0'|bool $bool_status
 *
 * @method static CustomerQuery findBySql($sql, $params = [])
 *
 * @property-read Profile $profile
 * @property-read Order[] $ordersPlain
 * @property-read Order[] $orders
 * @property-read Order[] $expensiveOrders
 * @property-read Order[] $ordersWithItems
 * @property-read Order[] $expensiveOrdersWithNullFK
 * @property-read Order[] $ordersWithNullFK
 * @property-read Order[] $orders2
 * @property-read Item[] $orderItems
 * @property-read Item[] $orderItems2
 * @property-read Item[] $items
 */
class Customer extends ActiveRecord
{
    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 2;

    public $status2;

    public $sumTotal;

    public static function tableName()
    {
        return 'customer';
    }

    public function getProfile()
    {
        return $this->hasOne(Profile::class, ['id' => 'profile_id']);
    }

    public function getOrdersPlain()
    {
        return $this->hasMany(Order::class, ['customer_id' => 'id']);
    }

    public function getOrders()
    {
        return $this->hasMany(Order::class, ['customer_id' => 'id'])->orderBy('[[id]]');
    }

    public function getExpensiveOrders()
    {
        return $this->hasMany(Order::class, ['customer_id' => 'id'])->andWhere('[[total]] > 50')->orderBy('id');
    }

    public function getOrdersWithItems()
    {
        return $this->hasMany(Order::class, ['customer_id' => 'id'])->with('orderItems');
    }

    public function getExpensiveOrdersWithNullFK()
    {
        return $this->hasMany(OrderWithNullFK::class, ['customer_id' => 'id'])->andWhere('[[total]] > 50')->orderBy('id');
    }

    public function getOrdersWithNullFK()
    {
        return $this->hasMany(OrderWithNullFK::class, ['customer_id' => 'id'])->orderBy('id');
    }

    public function getOrders2()
    {
        return $this->hasMany(Order::class, ['customer_id' => 'id'])->inverseOf('customer2')->orderBy('id');
    }

    // deeply nested table relation
    public function getOrderItems()
    {
        $rel = $this->hasMany(Item::class, ['id' => 'item_id']);

        return $rel->viaTable('order_item', ['order_id' => 'id'], function (ActiveQuery $q) {
            $q->viaTable('order', ['customer_id' => 'id']);
        })->orderBy('id');
    }

    public function getOrderItems2()
    {
        return $this->hasMany(OrderItem::class, ['order_id' => 'id'])
            ->via('ordersPlain');
    }

    public function getItems()
    {
        return $this->hasMany(Item::class, ['id' => 'item_id'])
            ->via('orderItems2');
    }

    public function afterSave($insert, $changedAttributes): void
    {
        ActiveRecordTest::$afterSaveInsert = $insert;
        ActiveRecordTest::$afterSaveNewRecord = $this->isNewRecord;
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * {@inheritdoc}
     * @return CustomerQuery<static>
     */
    public static function find()
    {
        return new CustomerQuery(static::class);
    }
}
