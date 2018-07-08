<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yiiunit\data\ar;

/**
 * OrderItemWithConstructor.
 *
 * @property int $order_id
 * @property int $item_id
 * @property int $quantity
 * @property string $subtotal
 */
class OrderItemWithConstructor extends ActiveRecord
{
    public static function tableName()
    {
        return 'order_item';
    }

    public function __construct($item_id, $quantity)
    {
        $this->item_id = $item_id;
        $this->quantity = $quantity;
        parent::__construct();
    }

    public static function instance($refresh = false)
    {
        return self::instantiate([]);
    }

    public static function instantiate($row)
    {
        return (new \ReflectionClass(static::class))->newInstanceWithoutConstructor();
    }

    public function getOrder()
    {
        return $this->hasOne(OrderWithConstructor::class, ['id' => 'order_id']);
    }
}
