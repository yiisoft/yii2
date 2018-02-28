<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

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
 *
 * @method CustomerQuery findBySql($sql, $params = []) static
 */
class Customer extends ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

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
        return $this->hasMany(Order::class, ['customer_id' => 'id'])->orderBy('id');
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
        /* @var $rel ActiveQuery */
        $rel = $this->hasMany(Item::class, ['id' => 'item_id']);

        return $rel->viaTable('order_item', ['order_id' => 'id'], function ($q) {
            /* @var $q ActiveQuery */
            $q->viaTable('order', ['customer_id' => 'id']);
        })->orderBy('id');
    }

    public function afterSave($insert, $changedAttributes)
    {
        ActiveRecordTest::$afterSaveInsert = $insert;
        ActiveRecordTest::$afterSaveNewRecord = $this->isNewRecord;
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * {@inheritdoc}
     * @return CustomerQuery
     */
    public static function find()
    {
        return new CustomerQuery(\get_called_class());
    }
}
