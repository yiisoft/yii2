<?php

namespace yiiunit\data\ar;

/**
 * Description of TotalOrder
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 2.10
 */
class TotalOrder extends QueryRecord
{

    public function attributes()
    {
        return ['item_id', 'quantity', 'total'];
    }

    public static function query()
    {
        return OrderItem::find()
                ->select(['item_id', 'quantity' => 'sum([[quantity]])', 'total' => 'sum([[quantity]]*[[subtotal]])'])
                ->groupBy(['item_id']);
    }
}
