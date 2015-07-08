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
class OrderWithNullFK extends ActiveRecord
{
    public static function tableName()
    {
        return 'order_with_null_fk';
    }


}
