<?php

namespace yiiunit\data\ar;

/**
 * Class Order
 *
 * @property int $id
 * @property int $customer_id
 * @property int $created_at
 * @property string $total
 */
class OrderWithNullFK extends ActiveRecord
{
    public static function tableName()
    {
        return 'order_with_null_fk';
    }


}
