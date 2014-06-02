<?php

namespace yiiunit\data\ar\elasticsearch;

/**
 * Class Order
 *
 * @property integer $id
 * @property integer $customer_id
 * @property integer $created_at
 * @property string $total
 */
class OrderWithNullFK extends ActiveRecord
{
    public static function primaryKey()
    {
        return ['id'];
    }

    public function attributes()
    {
        return ['id', 'customer_id', 'created_at', 'total'];
    }

    public static function tableName()
    {
        return 'order_with_null_fk';
    }
}
